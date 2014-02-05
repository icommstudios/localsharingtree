<?php
/**
* Template for displaying search forms
*/
?>
<form role="search" method="get" class="searchform" id="searchform" action="<?php echo home_url( '/' ); ?>">
    <div>
        <input type="text" value="" name="s" id="s" />
        <input type="submit" id="searchsubmit" value="Search" />
    </div>
</form>