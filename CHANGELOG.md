# Changelog

All significant changes to this project will be documented in this file.

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
