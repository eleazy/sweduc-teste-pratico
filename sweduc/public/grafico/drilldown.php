<?php
$dados = $_POST;
if (!isset($dados['subtext'])) {
    $dados['subtext'] = $dados['text'];
}
for ($o = 0; $o < (is_countable($dados['series']) ? count($dados['series']) : 0); $o++) {
    for ($i = 0; $i < (is_countable($dados['series'][$o]['data']) ? count($dados['series'][$o]['data']) : 0); $i++) {
        $dados['series'][$o]['data'][$i] = (float) $dados['series'][$o]['data'][$i];
    }

    if (isset($dados['series'][$o]['index'])) {
        $dados['series'][$o]['index'] = (float) $dados['series'][$o]['index'];
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
                    title: {
                        text: '<?= $dados['subtext']; ?>'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b><br/>'
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y:.2f}%'
                        }
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
