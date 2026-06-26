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
     * Convert a band into a list of table rows, one per posy sub-row.
     *
     * @param BandItem $band
     * @param string $tag 'th' or 'td'
     * @param object|null $data
     * @param array $grid
     * @param string $rowClass extra css class for every generated row
     * @param bool $stack
     * @return array
     */
    protected function bandRows(BandItem $band, string $tag, ?object $data, array $grid, string $rowClass = '', bool $stack = false): array
    {
        if ($data === null) {
            return [];
        }

        // drop line widgets: the table provides its own separators.
        $columns = array_values(array_filter(
            $band->toHtmlData($this->defaultData, $data),
            fn(array $col) => ($col['tag'] ?? '') !== 'hr'
        ));
        if (empty($columns)) {
            return [];
        }

        // multiline: a single <tr> per record stacking the sub-values by posy.
        if ($stack) {
            return [$this->buildStackedRow($columns, $tag, $grid, $rowClass)];
        }

        // normal: one <tr> per posy sub-row.
        $rows = [];
        foreach ($this->bucketByPosy($columns) as $bucket) {
            $rows[] = $this->buildRow($bucket, $tag, $grid, $rowClass);
        }
        return $rows;
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
        sort($positions);

        $grid = [];
        foreach ($positions as $posx) {
            if (empty($grid) || $posx - end($grid) > self::POSX_TOLERANCE) {
                $grid[] = $posx;
            }
        }

        // a band with only lines would leave an empty grid; keep a single column.
        return empty($grid) ? [0] : $grid;
    }

    /**
     * Build a single table row from a set of columns sharing the same posy.
     * Columns are mapped to grid positions and the colspan is filled so the row
     * always spans the whole table width. When the columns collide on the grid
     * (or there are more than grid columns), the row degrades to a single
     * full-width summary cell.
     *
     * @param array $columns
     * @param string $tag
     * @param array $grid
     * @param string $rowClass
     * @return array
     */
    protected function buildRow(array $columns, string $tag, array $grid, string $rowClass): array
    {
        $total = count($grid);
        usort($columns, fn(array $a, array $b) => $a['posx'] <=> $b['posx']);

        $indexes = [];
        $seen = [];
        $collision = false;
        foreach ($columns as $column) {
            $idx = $this->gridIndex($column['posx'], $grid);
            if (isset($seen[$idx])) {
                $collision = true;
            }
            $seen[$idx] = true;
            $indexes[] = $idx;
        }

        if ($collision || count($columns) > $total) {
            return $this->summaryRow($columns, $tag, $total, $rowClass);
        }

        $cells = [];
        $prev = 0;
        $count = count($columns);
        foreach ($columns as $i => $column) {
            $idx = max($indexes[$i], $prev);
            if ($idx > $prev) {
                $cells[] = $this->cell($tag, [], $idx - $prev);
            }

            $next = ($i + 1 < $count) ? max($indexes[$i + 1], $idx + 1) : $total;
            $line = $this->line($column['value'], $column['class'], $column['style']);
            $cells[] = $this->cell($tag, [$line], $next - $idx);
            $prev = $next;
        }
        if ($prev < $total) {
            $cells[] = $this->cell($tag, [], $total - $prev);
        }

        return ['class' => $rowClass, 'cells' => $cells];
    }

    /**
     * Build a single table row for a multiline record: one <tr> where each grid
     * column stacks (by posy) all its sub-values. This avoids one <tr> per
     * sub-row, so the table draws separators only between records.
     *
     * @param array $columns
     * @param string $tag
     * @param array $grid
     * @param string $rowClass
     * @return array
     */
    protected function buildStackedRow(array $columns, string $tag, array $grid, string $rowClass): array
    {
        $total = count($grid);

        // group the columns by grid index, ordered by posy inside each cell.
        $stacks = [];
        foreach ($columns as $column) {
            $idx = $this->gridIndex($column['posx'], $grid);
            $stacks[$idx][] = $column;
        }
        ksort($stacks);
        foreach ($stacks as &$stack) {
            usort($stack, fn(array $a, array $b) => $a['posy'] <=> $b['posy']);
        }
        unset($stack);

        $cells = [];
        $prev = 0;
        $occupied = array_keys($stacks);
        foreach ($occupied as $i => $idx) {
            if ($idx > $prev) {
                $cells[] = $this->cell($tag, [], $idx - $prev);
            }

            $lines = [];
            foreach ($stacks[$idx] as $column) {
                $lines[] = $this->line($column['value'], $column['class'], $column['style']);
            }

            $next = isset($occupied[$i + 1]) ? max($occupied[$i + 1], $idx + 1) : $total;
            $cells[] = $this->cell($tag, $lines, $next - $idx);
            $prev = $next;
        }
        if ($prev < $total) {
            $cells[] = $this->cell($tag, [], $total - $prev);
        }

        return ['class' => $rowClass, 'cells' => $cells];
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

        $table = ['columns' => $columns, 'thead' => [], 'tbody' => [], 'tfoot' => []];

        // header -> thead
        $header = $group->getHeader(false);
        if ($header !== null) {
            $table['thead'] = $this->bandRows($header, 'th', $firstRow, $grid);
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

        // footer -> tfoot (calculated columns already accumulated above)
        $footer = $group->getFooter(false);
        if ($footer !== null) {
            $table['tfoot'] = $this->bandRows($footer, 'td', $firstRow, $grid, 'fw-bold', $stack);
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
     * Build a single full-width cell row joining all the column values.
     *
     * @param array $columns
     * @param string $tag
     * @param int $total
     * @param string $rowClass
     * @return array
     */
    protected function summaryRow(array $columns, string $tag, int $total, string $rowClass): array
    {
        $values = [];
        foreach ($columns as $column) {
            if ($column['value'] !== '') {
                $values[] = $column['value'];
            }
        }

        $line = $this->line(implode(' ', $values), $columns[0]['class'] ?? '', $columns[0]['style'] ?? '');
        $cell = $this->cell($tag, [$line], max(1, $total));
        return ['class' => $rowClass, 'cells' => [$cell]];
    }
}
