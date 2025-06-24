<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title><?=$form->get_title()?> by <?= $user->get_full_name() ?></title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_back,arrow_downward,arrow_upward,delete,edit,globe,playlist_add,public_off,share,visibility" >
        <script src="lib/jquery-3.7.1.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        <script src="lib/chart.js"></script>
        <script>

            const form_id = <?= $form->get_id() ?> ;
            const encoded_search = "<?= $encoded_search ?>" ;

            $(function() {
                direct_acces();
                pie_chart();
            });

            function direct_acces(){

                //on ajoute une question "blanche"
                let list_question = $('#question');
                let html_list_question = list_question.html();
                html_list_question = "<option value='0' " + ( <?= $idx ?> === '0' ? "selected": "" )
                    + "> -- question -- </option>" //on a rajouter la question "blanche"
                    + html_list_question ; //on rajoute la suite des question
                list_question.html(html_list_question);

                //on masque le bouton de submit
                let submit_button = $('#submit_button');
                submit_button.hide();

                //--- select direct de question
                $("#question").change(function() {
                    //quand une question est selec, on vat direct voir la question, plus besoin de confirmation
                    if(this.value != 0){
                        location.href="instance/analyze/"+form_id+"/"+this.value+"/"+encoded_search;
                    }else{
                        //si egal a zero, on cache ce qui est dans la div de stats (donc la tarte)
                        $('#div_stats').hide();
                    }
                });
            }

            function pie_chart() {
                let stats = <?= ($stats_jason != null) ? $stats_jason : "null" ?>;

                if (stats != null) {
                    //tableaux qui vont être donné au 'chart pie' pour qu'il construit la tarte
                    const list_value = [];
                    const list_data = [];
                    const list_percentage = [];

                    //remmplissage
                    for (let stat of stats) {
                        list_value.push(stat.value);
                        list_data.push(stat.count);
                        list_percentage.push(stat.percentage)
                    }

                    //on modifie la div qui contient les stats: on la remplace par la tarte
                    $('#div_stats').html("<canvas id='myChart'></canvas>");

                    //code pour def la tarte
                    const ctx = $('#myChart');

                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: list_value,
                            datasets: [{
                                label: '# total count',
                                data: list_data,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            plugins: {
                                tooltip: {

                                    callbacks: {
                                        label: function (context) {
                                            let index = context.dataIndex;
                                            return [ "Count = "+context.parsed , "Ratio = " + list_percentage[index] + "%"];
                                        }
                                    }
                                }
                            }

                        }
                    });
                }
            }
        </script>

    </head>
    <body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark"> 
        <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width: 700px; ">
            <div class="container-fluid">
                <a href="form/index/<?=$form->get_id()?>/<?=$encoded_search?>" >
                    <span class="material-symbols-outlined" style="font-size: 32px;">arrow_back</span>
                </a>
            </div>
        </nav>
        <main class="w-100 m-3" style="max-width: 700px">
            <div class="m-3">
                <h2 class='fw-bold'><?=$form->get_title()?></h2> 
                <h5 class='text-secondary  fst-italic'>by <?= $user->get_full_name() ?></h5>
                <div class="analyze_cbo_box">
                    <form action="instance/analyze/<?=$form->get_id()?>/<?=$encoded_search?>" method="post" id="cbo_select_question">
                        <select name="question" id="question" class="form-select">
                            <?php foreach($questions as $question):?>
                                <option value=<?=$question->get_idx()?> <?= ($question->get_idx() == $idx) ? "selected": "" ?>><?=$question->get_title()?></option>
                            <?php endforeach;?>
                        </select>
                        
                    </form>
                    <button id="submit_button" class="icons_header material-symbols-outlined" form="cbo_select_question" type="submit">visibility</button>
                </div>
                <?php if($stats != null): ?>
                <div id="div_stats" class="analyse_list">
                    <table class="table_values">
                        <tr>
                            <th>Value</th><th>Count</th><th>Ratio</th>
                        </tr>
                        <?php foreach($stats as $stat):?>
                            <tr>
                                <td class="column_value"><?=$stat->get_value()?></td> 
                                <td class="column_count_ratio"><?=$stat->get_count()?></td> 
                                <td class="column_count_ratio"><?=number_format($stat->get_count() / $stat->get_instance_count() * 100, 1)?>%</td> 
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </body>
</html>