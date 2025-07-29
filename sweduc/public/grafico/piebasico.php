<?php
$dados = $_POST;
if (!isset($dados['subtext'])) {
    $dados['subtext'] = $dados['text'];
}




for ($o = 0; $o < (is_countable($dados['series']) ? count($dados['series']) : 0); $o++) {
    for ($i = 0; $i < (is_countable($dados['series'][0]['data']['y']) ? count($dados['series'][0]['data']['y']) : 0); $i++) {
        $dados['pie']['data'][$i ]['y'] = (float) $dados['series'][0]['data']['y'][$i];
        $dados['pie']['data'][$i ]['name'] =  $dados['series'][0]['data']['name'][$i];
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
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '<?= $dados['text']; ?>'
                },
//                    subtitle: {
//                        text: 'Source: WorldClimate.com'
//                    },

                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.2f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.2f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Brands',
                    colorByPoint: true,

                    data: <?php echo json_encode($dados['pie']['data'], JSON_THROW_ON_ERROR); ?>
                }],
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
