<?php
$dados = $_POST;
if (!isset($dados['subtext'])) {
    $dados['subtext'] = $dados['text'];
}
for ($o = 0; $o < (is_countable($dados['series']) ? count($dados['series']) : 0); $o++) {
    for ($i = 0; $i < (is_countable($dados['series'][$o]['data']) ? count($dados['series'][$o]['data']) : 0); $i++) {
        $dados['series'][$o]['data'][$i] = (int) $dados['series'][$o]['data'][$i];
    }

    if (isset($dados['series'][$o]['index'])) {
        $dados['series'][$o]['index'] = (int) $dados['series'][$o]['index'];
    }
}

?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?= $dados['text']; ?></title>

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

        <script type="text/javascript">
            $(function () {
                $('#container').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '<?= $dados['text']; ?>'
                    },
//                    subtitle: {
//                        text: 'Source: WorldClimate.com'
//                    },
                    xAxis: {
                        categories: <?= json_encode($dados['categories'], JSON_THROW_ON_ERROR); ?>,
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        allowDecimals: false,
                        title: {
                            text: '<?= $dados['subtext']; ?>'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.0f} </b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: <?php echo json_encode($dados['series'], JSON_THROW_ON_ERROR); ?>,
                    exporting: {
                        sourceWidth: 1200,
                        sourceHeight: 700,
                        scale: 1,
                        chartOptions: {
                            subtitle: null
                        }
                    }
                    
                });
            });
        </script>


    </head>
    <body>
        <script src="https://<?= $_SERVER['SERVER_NAME'] ?>/js/highcharts/highcharts.js"></script>
        <script src="https://<?= $_SERVER['SERVER_NAME'] ?>/js/highcharts/exporting.js"></script>
        <script src="https://<?= $_SERVER['SERVER_NAME'] ?>/js/highcharts/estilo.js"></script>

        <div id="container" style=" margin: 0"></div>

    </body>
</html>
