<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2026 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2026 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public license as
 * published by the Free Software Foundation, either version 3 of the
 * license, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public license for more details.
 *
 * You should have received a copy of the GNU Lesser General Public license
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use FacturaScripts\Core\Html;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Main class for generate an on-screen HTML report from a XML Report file.
 *
 * It reuses the same XML + ModelReport + bands/widgets than the PDF/CSV
 * templates, but interprets them semantically (not by coordinates):
 *   - header -> thead          - detail -> tbody rows
 *   - subgroup header -> section row    - subgroup footer -> subtotal row
 *   - footer -> tfoot          - column posx -> table column (grid)
 *   - column posy -> sub-row inside a band
 *
 * The geometry (posx/posy) is only used as a hint to align cells; line widgets
 * and absolute coordinates are dropped because the HTML table provides its own
 * layout and borders.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class HtmlTemplate extends ExportTemplate
{
    /** Tolerance, in XML points, to merge near columns into the same grid column. */
    private const POSX_TOLERANCE = 8;

    /** Tolerance, in XML points, to merge near columns into the same band sub-row. */
    private const POSY_TOLERANCE = 8;

    /** Twig block that paints the prepared structure (included by a view). */
    private const TEMPLATE = '@PluginExtendedReport/Block/ReportHtml.html.twig';

    /**
     * Build the HTML output according to the template loaded.
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(): string
    {
        if (empty($this->groups)) {
            return '';
        }

        $tables = [];
        foreach ($this->groups as $group) {
            $tables[] = $this->renderGroup($group);
        }

        return Html::render(self::TEMPLATE, ['tables' => $tables]);
    }

    /**
     * Assign a set of columns to the grid columns, returning one bucket of
     * widgets per grid column (most are empty or hold a single widget). It runs
     * in two passes so a label that floats between two data columns (e.g. the
     * "Totals:" caption) does not collide with the anchored values:
     *   1. anchors: columns whose posx matches a grid column take that column.
     *   2. floating: the rest land on the nearest still-free grid column.
     *
     * @param array $columns
     * @param array $grid
     * @return array list indexed by grid position, each item a list of columns
     */
    protected function assignToColumns(array $columns, array $grid): array
    {
        $slots = array_fill(0, count($grid), []);
        $floating = [];

        // pass 1: anchor the columns aligned with a grid column.
        foreach ($columns as $column) {
            $idx = $this->gridIndex($column['posx'], $grid);
            if (abs($column['posx'] - $grid[$idx]) <= self::POSX_TOLERANCE) {
                $slots[$idx][] = $column;
            } else {
                $floating[] = $column;
            }
        }

        // pass 2: place the floating columns in the nearest free slot.
        foreach ($floating as $column) {
            $idx = $this->nearestFreeSlot($column['posx'], $grid, $slots);
            $slots[$idx][] = $column;
        }

        return $slots;
    }

    /**
     * Convert a band into a list of table rows, one per posy sub-row.
     *
     * @param BandItem $band
     * @param string $tag 'th' or 'td'
     * @param object|null $data
     * @param array $grid
     * @param string $rowClass extra css class for every generated row
     * @param bool $stack stack the posy sub-values inside a single <tr>
     * @return array
     */
    protected function bandRows(BandItem $band, string $tag, ?object $data, array $grid, string $rowClass = '', bool $stack = false): array
    {
        if ($data === null) {
            return [];
        }

        // drop widgets that have no meaning on screen (lines, page number,
        // skipped labels). The table provides its own separators.
        $columns = array_values(array_filter(
            $band->toHtmlData($this->defaultData, $data),
            fn(array $col) => false === $this->isSkipped($col)
        ));
        if (empty($columns)) {
            return [];
        }

        // multiline: a single <tr> per record stacking the sub-values by posy.
        if ($stack) {
            return [$this->rowFromColumns($columns, $tag, $grid, $rowClass)];
        }

        // normal: one <tr> per posy sub-row.
        return $this->columnsToRows($columns, $tag, $grid, $rowClass);
    }

    /**
     * Group the band columns into sub-rows by their posy value.
     *
     * @param array $columns
     * @return array
     */
    protected function bucketByPosy(array $columns): array
    {
        usort($columns, fn(array $a, array $b) => ($a['posy'] <=> $b['posy']) ?: ($a['posx'] <=> $b['posx']));

        $buckets = [];
        $current = [];
        $anchor = null;
        foreach ($columns as $column) {
            if ($anchor === null || $column['posy'] - $anchor > self::POSY_TOLERANCE) {
                if (false === empty($current)) {
                    $buckets[] = $current;
                }
                $current = [];
                $anchor = $column['posy'];
            }
            $current[] = $column;
        }
        if (false === empty($current)) {
            $buckets[] = $current;
        }

        return $buckets;
    }

    /**
     * Build the list of grid columns (representative posx) from the detail band.
     * Near columns (within POSX_TOLERANCE) collapse into a single grid column.
     *
     * @param BandItem $detail
     * @return array
     */
    protected function buildGrid(BandItem $detail): array
    {
        $positions = [];
        foreach ($detail->columns as $column) {
            if ($column->widget->getType() === 'line') {
                continue;
            }
            $positions[] = $column->posx;
        }

        return $this->gridFromPositions($positions);
    }

    /**
     * Build a table cell structure. A cell holds one or more stacked lines
     * (sub-values); a normal cell has a single line, a multiline record cell
     * stacks several. This keeps one <tr> per data record (no separator lines
     * between sub-rows, matching the PDF).
     *
     * @param string $tag
     * @param array $lines    list of line structures (see line())
     * @param int $colspan
     * @return array
     */
    protected function cell(string $tag, array $lines, int $colspan): array
    {
        return [
            'tag' => $tag,
            'lines' => $lines,
            'colspan' => $colspan,
        ];
    }

    /**
     * Build the summary cards from the footer columns flagged area="cards". Each
     * card pairs the widget title (its caption) with the already accumulated
     * value, so a numeric total is never shown without a label. The cards are
     * collected independently of hideonview: a card column keeps showing in the
     * tfoot row unless the author also flags it hideonview (then it is a card
     * only). The cardcolor is a Bootstrap contextual color (success, info...)
     * the template turns into the pastel '-subtle' variant; it defaults to
     * 'secondary'.
     *
     * @param BandItem $footer
     * @param object|null $data
     * @return array
     */
    protected function collectStats(BandItem $footer, ?object $data): array
    {
        if ($data === null) {
            return [];
        }

        $stats = [];
        foreach ($footer->toHtmlData($this->defaultData, $data) as $column) {
            if (($column['area'] ?? '') !== 'cards') {
                continue;
            }

            $stats[] = [
                'title' => Tools::trans($column['title'] ?? ''),
                'value' => $column['value'] ?? '',
                'color' => empty($column['cardcolor']) ? 'secondary' : $column['cardcolor'],
            ];
        }

        return $stats;
    }

    /**
     * Turn a flat list of columns into table rows, one per posy sub-row, mapping
     * each column to its grid position.
     *
     * @param array $columns
     * @param string $tag
     * @param array $grid
     * @param string $rowClass
     * @return array
     */
    protected function columnsToRows(array $columns, string $tag, array $grid, string $rowClass = ''): array
    {
        if (empty($columns)) {
            return [];
        }

        $rows = [];
        foreach ($this->bucketByPosy($columns) as $bucket) {
            $rows[] = $this->rowFromColumns($bucket, $tag, $grid, $rowClass);
        }
        return $rows;
    }

    /**
     * Build a grid (representative posx) from a list of posx positions, merging
     * near positions (within POSX_TOLERANCE) into the same column.
     *
     * @param array $positions
     * @return array
     */
    protected function gridFromPositions(array $positions): array
    {
        sort($positions);

        $grid = [];
        foreach ($positions as $posx) {
            if (empty($grid) || $posx - end($grid) > self::POSX_TOLERANCE) {
                $grid[] = $posx;
            }
        }

        // keep at least one column so degenerate bands still render.
        return empty($grid) ? [0] : $grid;
    }

    /**
     * Get the nearest grid column index for a given posx.
     *
     * @param int $posx
     * @param array $grid
     * @return int
     */
    protected function gridIndex(int $posx, array $grid): int
    {
        $best = 0;
        $bestDiff = PHP_INT_MAX;
        foreach ($grid as $index => $gridPosx) {
            $diff = abs($posx - $gridPosx);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $index;
            }
        }
        return $best;
    }

    /**
     * Indicate if the rupture field has value in the given row.
     *
     * @param string $field
     * @param object $row
     * @return bool
     */
    protected function hasGroupValue(string $field, object $row): bool
    {
        $value = $row->{$field} ?? null;
        return $value !== null && $value !== '';
    }

    /**
     * Indicate if a column must be dropped from the HTML output: line widgets
     * (the table draws its own separators) and columns flagged hideonview.
     *
     * @param array $column
     * @return bool
     */
    protected function isSkipped(array $column): bool
    {
        return ($column['tag'] ?? '') === 'hr'
            || ($column['hideonview'] ?? false);
    }

    /**
     * Build a single stacked line (sub-value) inside a cell.
     *
     * @param string $value
     * @param string $class
     * @param string $style
     * @return array
     */
    protected function line(string $value, string $class, string $style): array
    {
        return [
            'value' => $value,
            'class' => $class,
            'style' => $style,
        ];
    }

    /**
     * Find the nearest free grid slot for a given posx. When every slot is
     * already taken it falls back to the nearest column (the widget will stack).
     *
     * @param int $posx
     * @param array $grid
     * @param array $slots
     * @return int
     */
    protected function nearestFreeSlot(int $posx, array $grid, array $slots): int
    {
        $best = null;
        $bestDiff = PHP_INT_MAX;
        foreach ($grid as $index => $gridPosx) {
            if (false === empty($slots[$index])) {
                continue;
            }
            $diff = abs($posx - $gridPosx);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $index;
            }
        }

        return $best ?? $this->gridIndex($posx, $grid);
    }

    /**
     * Build one table structure for a top-level group.
     *
     * @param GroupItem $group
     * @return array
     */
    protected function renderGroup(GroupItem $group): array
    {
        $detail = $group->getDetail();
        $grid = $this->buildGrid($detail);
        $columns = count($grid);

        $model = $this->datasets[$group->name] ?? $this->datasets['main'] ?? null;
        $firstRow = ($model !== null && false === empty($model->data)) ? reset($model->data) : null;

        $table = ['columns' => $columns, 'meta' => [], 'stats' => [], 'thead' => [], 'tbody' => [], 'tfoot' => []];

        // header -> metadata block (area="meta") + column titles (thead)
        $header = $group->getHeader(false);
        if ($header !== null) {
            $split = $this->splitHeader($header, $firstRow, $grid);
            $table['meta'] = $split['meta'];
            $table['thead'] = $split['thead'];
        }

        // multiline detail (and its mirror footer) stack sub-values inside one <tr>.
        $stack = $detail->subRows > 1;

        // detail (with optional subgroups) -> tbody
        $detailGroup = $group->getDetailGroup();
        if ($detailGroup !== null) {
            $table['tbody'] = $this->renderSubgroup($group, $detailGroup, $model, $grid);
        } else {
            $footer = $group->getFooter(false);
            foreach ($model->data ?? [] as $row) {
                $table['tbody'] = array_merge($table['tbody'], $this->bandRows($detail, 'td', $row, $grid, '', $stack));
                $footer?->calculate($row);
            }
        }

        // footer -> tfoot (calculated columns already accumulated above) + summary
        // cards (area="cards") shown above the table. Both read the same already
        // accumulated footer band, so the totals are final on this single pass.
        $footer = $group->getFooter(false);
        if ($footer !== null) {
            $table['tfoot'] = $this->bandRows($footer, 'td', $firstRow, $grid, 'fw-bold', $stack);
            $table['stats'] = $this->collectStats($footer, $firstRow);
        }

        return $table;
    }

    /**
     * Build the tbody rows for a group whose detail is itself a group (subtotals).
     *
     * @param GroupItem $group
     * @param GroupItem $detailGroup
     * @param ModelReport|null $model
     * @param array $grid
     * @return array
     */
    protected function renderSubgroup(GroupItem $group, GroupItem $detailGroup, ?ModelReport $model, array $grid): array
    {
        $detail = $detailGroup->getDetail();
        $subHeader = $detailGroup->getHeader(false);
        $subFooter = $detailGroup->getFooter(false);
        $groupFooter = $group->getFooter(false);

        $rows = [];
        $data = $model->data ?? [];
        $previousRow = null;
        $stack = $detail->subRows > 1;

        $first = reset($data);
        if ($first !== false && $subHeader !== null && $this->hasGroupValue($detail->fieldName, $first)) {
            $rows = array_merge($rows, $this->bandRows($subHeader, 'th', $first, $grid, 'table-active'));
        }

        foreach ($data as $row) {
            $hasRupture = $detail->hasFieldRupture($row, true);
            if ($hasRupture && $previousRow !== null) {
                if ($subFooter !== null) {
                    $rows = array_merge($rows, $this->bandRows($subFooter, 'td', $previousRow, $grid, 'table-active'));
                    $subFooter->reset();
                }
                if ($subHeader !== null && $detail->hasFieldValue($row)) {
                    $rows = array_merge($rows, $this->bandRows($subHeader, 'th', $row, $grid, 'table-active'));
                }
            }

            $previousRow = $row;
            $rows = array_merge($rows, $this->bandRows($detail, 'td', $row, $grid, '', $stack));
            $subFooter?->calculate($row);
            $groupFooter?->calculate($row);
        }

        if ($previousRow !== null && $subFooter !== null) {
            $rows = array_merge($rows, $this->bandRows($subFooter, 'td', $previousRow, $grid, 'table-active'));
            $subFooter->reset();
        }

        return $rows;
    }

    /**
     * Build a single table row from a set of columns, one cell per grid column
     * (empty cells fill the gaps so every band aligns to the detail grid). A
     * lone value spans the whole row (report title, section header). When a cell
     * holds several columns (a multiline record) they stack by posy.
     *
     * @param array $columns
     * @param string $tag
     * @param array $grid
     * @param string $rowClass
     * @return array
     */
    protected function rowFromColumns(array $columns, string $tag, array $grid, string $rowClass): array
    {
        // a lone value spans the whole row (report title, section header...).
        if (count($columns) === 1) {
            $line = $this->line($columns[0]['value'], $columns[0]['class'], $columns[0]['style']);
            return ['class' => $rowClass, 'cells' => [$this->cell($tag, [$line], max(1, count($grid)))]];
        }

        $cells = [];
        foreach ($this->assignToColumns($columns, $grid) as $widgets) {
            usort($widgets, fn(array $a, array $b) => $a['posy'] <=> $b['posy']);
            $lines = [];
            foreach ($widgets as $column) {
                $lines[] = $this->line($column['value'], $column['class'], $column['style']);
            }
            $cells[] = $this->cell($tag, $lines, 1);
        }

        return ['class' => $rowClass, 'cells' => $cells];
    }

    /**
     * Split the header band into report metadata and column titles. Columns
     * flagged area="meta" (company, date, filters, title...) go to a separate
     * borderless block above the table; the rest become the table <thead>.
     *
     * @param BandItem $header
     * @param object|null $data
     * @param array $grid
     * @return array ['meta' => rows, 'thead' => rows]
     */
    protected function splitHeader(BandItem $header, ?object $data, array $grid): array
    {
        if ($data === null) {
            return ['meta' => [], 'thead' => []];
        }

        $meta = [];
        $titles = [];
        foreach ($header->toHtmlData($this->defaultData, $data) as $column) {
            if ($this->isSkipped($column)) {
                continue;
            }
            if (($column['area'] ?? '') === 'meta') {
                $meta[] = $column;
            } else {
                $titles[] = $column;
            }
        }

        // metadata uses its own grid (its posx do not match the data columns).
        $metaGrid = $this->gridFromPositions(array_column($meta, 'posx'));

        return [
            'meta' => $this->columnsToRows($meta, 'td', $metaGrid),
            'thead' => $this->columnsToRows($titles, 'th', $grid),
        ];
    }
}
