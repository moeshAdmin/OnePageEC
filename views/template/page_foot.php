<!-- ªí§À¸ê°T (ex. Copyright, Contact Info, ...) -->
<?php
	// Custom JS Files
	if( isset($this->my_template->asset['footer']['js'])) {
		foreach($this->my_template->get_js('footer') as $js_file) {
			echo $js_file.PHP_EOL;
		}
	}
?>
</div>
</body>

<script type="text/javascript" src='../assets/vueInit.js'></script>

</html>