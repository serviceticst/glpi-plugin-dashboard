<?php
include ("../../../../inc/includes.php");

Html::header(
  'Alertas',
  $_SERVER['PHP_SELF'],
  'plugins',
  'dashboard',
  'alertas'
);
?>

<div class="center">
    <h2>Alertas</h2>
    <button id="alert-btn" class="submit">Alerta chamados em atraso</button>
</div>
<audio id="alert-audio" src="../sounds/alert.mp3" preload="auto"></audio>

<script>
document.getElementById('alert-btn').onclick = function() {
    document.getElementById('alert-audio').play();
};
</script>

<?php
Html::footer();
?>