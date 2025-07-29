<?php
$dados = $_POST;

if (!isset($dados['subtext'])) {
    $dados['subtext'] = $dados['text'];
}
for ($o = 0; $o < (is_countable($dados['series']) ? count($dados['series']) : 0); $o++) {
    for ($i = 0; $i < (is_countable($dados['series'][$o]['data']) ? count($dados['series'][$o]['data']) : 0); $i++) {
        $dados['series'][$o]['data'][$i] = (int) $dados['series'][$o]['data'][$i];
    }
    $dados['series'][$o]['index'] = (int) $dados['series'][$o]['index'];
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
                        type: 'bar'
                    },
                    title: {
                        text: '<?= $dados['text']; ?>'
                    },
                    xAxis: {
                        categories: <?= json_encode($dados['categories'], JSON_THROW_ON_ERROR); ?>
                    },
                    yAxis: {
                        min: 0,
                        allowDecimals: false,
                        title: {
                            text: '<?= $dados['subtext']; ?>'
                        }
                    },
                    legend: {
                        reversed: true
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                    exporting: {
                        enabled: true,
                        printMaxWidth: 1200,
                        sourceWidth: 900,
                        scale:1
                    },
                    series: <?php echo json_encode($dados['series'], JSON_THROW_ON_ERROR); ?>,
                    exporting: {
                        sourceWidth: 1200,
                        sourceHeight: 500,
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

        <div id="container" style="margin: 0"></div>

    </body>
</html>
