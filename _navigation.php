<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo (isset($list) && !empty($list)) ? ('?list=' . $list . '&locale=' . Config::getLocale()) : '#'; ?>">PML<sup>3</sup></a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><p class="navbar-text"><?php echo $list; ?></p></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if (PhpMailingList::isLoggedIn()) : ?>
                    <li><a href="?list=<?php echo $list ?>&showModule=send&locale=<?php echo Config::getLocale(); ?>"><?php echo Config::__('NewMessage'); ?></a></li>
                    <li><a href="?list=<?php echo $list ?>&showModule=admin&locale=<?php echo Config::getLocale(); ?>"><?php echo Config::__('Admin'); ?></a></li>
                    <li><a href="?list=<?php echo $list ?>&showModule=logout&locale=<?php echo Config::getLocale(); ?>"><?php echo Config::__('Logout'); ?></a></li>
                <?php endif; ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo Config::__('Language'); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo PhpMailingList::getLocaleUrl('en_US'); ?>" class="<?php echo mb_strpos(Config::getLocale(), 'en_') !== false ? 'active' : '' ?>"><span class="lang-sm lang-lbl" lang="en"></span></a></li>
                        <li><a href="<?php echo PhpMailingList::getLocaleUrl('de_AT'); ?>" class="<?php echo mb_strpos(Config::getLocale(), 'de_') !== false ? 'active' : '' ?>"><span class="lang-sm lang-lbl" lang="de"></span></a></li>
                    </ul>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>