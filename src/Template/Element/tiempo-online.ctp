<script>
    setInterval(function() {
        timespan = countdown(new Date(<?php echo $this->Time->format($tiempo, 'YY'); ?>,<?php echo $this->Time->format($tiempo, 'MM'); ?>,<?php echo $this->Time->format($tiempo, 'dd'); ?>,<?php echo $this->Time->format($tiempo, 'HH') ?>,<?php echo $this->Time->format($tiempo, 'mm') ?>,<?php echo $this->Time->format($tiempo, 'ss') ?>), new Date());
        div = document.getElementById('time-<?php echo $posicion; ?>');
        div.innerHTML = timespan.days + " d " + timespan.hours + " h " + timespan.minutes + " m " + timespan.seconds + " s "
    }, 1000);
</script>
<span id="time-<?php echo $posicion; ?>"></span>

