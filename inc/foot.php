        <div id='search-input-overlay' class='hidden'>
            <div>
                <h5>Look up a verse:</h5>
                <input id='search-input'>
                <div id='search-results'></div>
            </div>
        </div>
        <script src='/res/js/search.js?v=<?= COMMIT_HASH ?>'></script>
        <script src='/res/js/global.js?v=<?= COMMIT_HASH ?>'></script>
        <!--
	    This is solely a personal project.
            If you have questions, concerns, or find a mistake, please email me: abe(at)ramseyer(dot)dev.
            Page generated in <?= number_format(microtime(true) - $time, 4) ?> sec
        -->
    </body>
</html>
