var isjQueryReady = false;
var isGoogleChartsReady = false;
var isInitialized = false;
var response_content;

google.charts.load('current', {'packages': ['line']});
google.charts.setOnLoadCallback(googleChartsLoadCallback);

function googleChartsLoadCallback() {
    isGoogleChartsReady = true;
    init();
}

jQuery(document).ready(function () {
    isjQueryReady = true;
    jQuery('.chart-type').select2();
    init();

});

function init() {
    if (!isjQueryReady || !isGoogleChartsReady || isInitialized) {
        return;
    }
    isInitialized = true;

    jQuery('.chart-period').change(function () {
        var val = jQuery('.chart-period').val();
        var now = new Date();
        if (val === 'this_week') {
            now.setDate(now.getDate() - now.getDay() + 1);
            jQuery('.chart-period-start').val(format_date(now));
            now.setDate(now.getDate() + 6);
            jQuery('.chart-period-end').val(format_date(now));
        } else if (val === 'this_month') {
            var start_month = new Date(now.getFullYear(), now.getMonth(), 1);
            jQuery('.chart-period-start').val(format_date(start_month));
            var end_month = new Date(now.getFullYear(), now.getMonth() + 1, -1);
            jQuery('.chart-period-end').val(format_date(end_month));
        }
    });

    jQuery('.chart-period-start, .chart-period-end').change(function () {
        jQuery('.chart-period').val('custom');
    });

    jQuery('.chart-options').submit(function (e) {
        e.preventDefault();

        var params = jQuery(this).serialize();
        showChart(params);

        return false;
    });

    jQuery(window).resize(function () {
        if (response_content.data.columns.length > 1) {
            renderChart(response_content.data);
        }
    });

    jQuery('.chart-period').change();
    jQuery('.chart-options').submit();
}

function renderChart(data) {
    var datatable = new google.visualization.DataTable();

    if (data.columns) {
        data.columns.forEach(function (item, i) {
            datatable.addColumn(0 === i ? 'string' : 'number', item);
        });
    }

    if (data.rows) {
        datatable.addRows(Object.values(data.rows));

        var formatter = new google.visualization.NumberFormat({
            fractionDigits: 2
        });

        formatter.format(datatable, 1);
    }

    var chart_width = jQuery("#chart-container").width();
    var chart_height = jQuery(window).height() - jQuery("#chart-container").offset().top;

    if (200 > chart_height) {
        chart_height = 200;
    }


    var options = {
        chart: {
            title: data.title ? data.title : '',
            subtitle: data.subtitle ? data.subtitle : '',
        },
        width: chart_width,
        height: chart_height,
    };

    var chart = new google.charts.Line(document.getElementById('chart-container'));

    chart.draw(datatable, options);
}

function showChart(params) {
   /* jQuery('.update-chart').prop('disabled', true);*/
    /*jQuery('.chart-placeholder').addClass('loading');*/
    let loader = jQuery('.woo_discount_loader');
    loader.show();

    jQuery.post(
        ajaxurl,
        {
            action: 'wdr_admin_statistics',
            method: 'get_chart_data',
            params: params,
        },
        function (response) {
            loader.hide();
            /*jQuery('.update-chart').prop('disabled', false);*/
          /*  jQuery('.chart-placeholder').removeClass('loading');*/
            if (response.success) {
                if (response.data.columns.length > 1) {
                    response_content = response;
                    renderChart(response.data);
                } else {
                    jQuery('#chart-container').html(wdr_data.localization_data.chart_data);
                }
            }else {
                jQuery('#chart-container').html(wdr_data.localization_data.chart_data);
            }
        },
        'json'
    );
}

function to2Digits(num) {
    return num < 10 ? ('0' + num) : num;
}

function format_date(date) {
    return [
        to2Digits(date.getFullYear()),
        to2Digits(date.getMonth() + 1),
        to2Digits(date.getDate())
    ].join('-');
}