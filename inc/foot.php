        <div id='search-input-overlay' class='hidden'>
            <div>
                <h5>Look up a verse:</h5>
                <h6 id='search-input'></h6>
                <div id='search-results'></div>
            </div>
        </div>
        <script src='/res/js/search.js'></script>
        <script src='/res/js/global.js'></script>
        <!--
	    This is solely a personal project.
            If you have questions, concerns, or find a mistake, please email me: abe(at)ramseyer(dot)dev.
            Page load in <?= number_format(microtime(true) - $time, 4) ?> seconds
        -->
    </body>
</html>
