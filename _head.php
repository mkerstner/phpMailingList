<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="favicon.ico">

        <title><?php echo Config::__('MailingList') . ': ' . $list; ?> | phpMailingList</title>

        <link href="web/css/bootstrap.min.css" rel="stylesheet">
        <link href="web/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="web/languages.min.css" rel="stylesheet">
        <link href="web/css/style.css" rel="stylesheet">

        <script src="web/js/jquery-2.1.4.min.js"></script>
        <script src="web/js/bootstrap.min.js"></script>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <?php if (Config::get('google_analytics_tracking_id') !== '') : ?>
            <script>
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                            m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
                ga('create', '<?php echo Config::get('google_analytics_tracking_id'); ?>', 'auto');
                ga('send', 'pageview');
            </script>
        <?php endif; ?>
    </head>