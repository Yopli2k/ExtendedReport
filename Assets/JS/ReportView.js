/**
 * This file is part of Extended Report plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
$(document).ready(function () {
    $("a[name='ReportDataTab'").on("show.bs.tab", function (e) {
        var button = $(e.target).data("view") + "Buttons";
        $("#" + button).show();
    });
    $("a[name='ReportDataTab'").on("hide.bs.tab", function (e) {
        var button = $(e.target).data("view") + "Buttons";
        $("#" + button).hide();
    });
});