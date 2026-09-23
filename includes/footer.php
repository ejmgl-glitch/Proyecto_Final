</main>
    <div 
        id="react-footer-root" 
        data-baseurl="<?= htmlspecialchars(defined('BASE_URL') ? BASE_URL : '/chileroPasos', ENT_QUOTES, 'UTF-8') ?>"
    ></div>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script type="text/babel" src="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/chileroPasos' ?>/assets/js/components/Footer.jsx"></script>
</body>
</html>