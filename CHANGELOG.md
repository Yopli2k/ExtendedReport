# Changelog

All significant changes to this project will be documented in this file.

## [2.11] - 2026-06-29

### New Features and Improvements

- New summary cards (statistics header) in the HTML report viewer. Any footer total can be highlighted as a pastel card above the table, so key figures (totals, counts…) are visible at a glance without scrolling to the footer.
    - New column area `area="cards"`: marks a footer total to be rendered as a summary card. It is additive — the total keeps showing in the footer row unless the column is also flagged `hideonview="true"` (then it appears only as a card).
    - New widget attribute `title` — caption of the card (translated via `Tools::trans`), so the figure always has a label.
    - New widget attribute `cardcolor` — Bootstrap contextual color of the card (`primary`, `secondary`, `success`, `info`, `warning`, `danger`), painted with its pastel `bg-*-subtle` variant, which also respects the theme's dark mode. Defaults to `secondary`.
    - No JavaScript involved: footer totals are already accumulated when the HTML is generated (the footer is evaluated after iterating every row), so the cards are painted on top with the final values in a single server-side pass.
    - `area`, `title` and `cardcolor` are ignored by the PDF and CSV outputs.

## [2.10] - 2026-06-27

### New Features and Improvements

- Added on-screen HTML report viewer. The same XML design and data model used for PDF/CSV can now be rendered as a responsive HTML table directly in the browser, without downloading any file.
    - New `HtmlTemplate` engine that interprets the XML semantically: header → `thead`, detail rows → `tbody`, footer → `tfoot`, subgroup headers/footers → section and subtotal rows.
    - New `ReportHtmlViewer` controller that opens the HTML report in a new browser tab.
    - New reusable Twig block (`View/Block/ReportHtml.html.twig`) that paints the report table and can be embedded in any plugin view.
    - Bootstrap utility classes, colors and background colors declared in the XML are carried through to the HTML output.

- New XML column attributes for fine-grained control of the HTML output:
    - `area="meta"` — marks header columns as metadata (company name, report title, date…). They are rendered in an info block above the table instead of as a table row. Ignored by the PDF.
    - `hideonview="true"` — hides the column from the HTML viewer (useful for page-number or decorative columns).
    - `hideonpdf="true"` — hides the column from the PDF output (useful for columns intended only for the HTML view).

- New `subrows` attribute on `<detail>` — declares how many stacked sub-rows a detail row contains in the HTML view, enabling correct vertical alignment of cells that share the same row but have different `posy` values. Ignored by the PDF.

- New `class` attribute on `<widget>` — passes extra CSS classes (e.g. Bootstrap utilities such as `h2`, `fw-bold`, `text-end`) to the HTML cell. Ignored by the PDF.

- Footer and subgroup footer alignment in the HTML view is now handled by a two-pass column-assignment algorithm: anchored widgets are placed first in the grid column whose `posx` matches, then floating labels (such as "Totals…") are placed in the nearest free slot. This produces correctly aligned footer rows without any extra XML attributes.
