<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Jon Arano <arano.jon@gmail.com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if (! $current_user->admin) do_error(_('Esta página es sólo para administradores'), 403);

do_header(_('Estadísticas | Jonéame'));

$users = $db->get_results("
	select date_format(user_date, '%m/%Y') as month, count(*) as count from users
	group by year(user_date), month(user_date)
");
$users_formatted = "";
foreach ($users as $month) {
	$users_formatted .= sprintf("['%s', %d],", $month->month, $month->count);
}

$links = $db->get_results("
	select date_format(link_sent_date, '%m/%Y') as month, count(*) as count, sum(link_status='published') as count2
	from links group by year(link_sent_date), month(link_sent_date);
");
$links_formatted = "";
foreach ($links as $month) {
	$links_formatted .= sprintf("['%s', %d, %d],", $month->month, $month->count, $month->count2);
}

$comments = $db->get_results("
	select date_format(comment_date, '%m/%Y') as month, count(*) as count, sum(comment_votes > 1) as count2, sum(comment_votes > 3) as count3
	from comments group by year(comment_date), month(comment_date);
");
$comments_formatted = "";
foreach ($comments as $month) {
	$comments_formatted .= sprintf("['%s', %d, %d, %d],", $month->month, $month->count, $month->count2, $month->count3);
}
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var users = google.visualization.arrayToDataTable([
          ['Mes', 'Registrados'], <?php echo $users_formatted; ?>
        ]);
        var links = google.visualization.arrayToDataTable([
          ['Mes', 'Enviadas', 'Publicadas'], <?php echo $links_formatted; ?>
        ]);
        var comments = google.visualization.arrayToDataTable([
          ['Mes', 'Escritos', 'Con al menos 1 voto', 'Con al menos 3 votos'], <?php echo $comments_formatted; ?>
        ]);

        var users_chart = new google.visualization.LineChart(document.getElementById('users_chart_div'));
        users_chart.draw(users);
        var links_chart = new google.visualization.LineChart(document.getElementById('links_chart_div'));
        links_chart.draw(links);
        var comments_chart = new google.visualization.LineChart(document.getElementById('comments_chart_div'));
        comments_chart.draw(comments);
      }
    </script>

<h3>Usuarios</h3>
    <div id="users_chart_div" style="width: 1200px; height: 500px;"></div>

<h3>Noticias</h3>
    <div id="links_chart_div" style="width: 1200px; height: 500px;"></div>

<h3>Comentarios</h3>
    <div id="comments_chart_div" style="width: 1200px; height: 500px;"></div>


<?php
do_footer();
