<?php
/* 
Purpose: Quick Link Editor (shadowbox)
*/
global $meerkat_ql, $js; ?>

<div id="quicklinks">

    <?php if( is_super_admin() ){ ?>
        <div class="test">
            <a class="edit-me show-cookie" href="javascript:void(0)" title="show cookies">show</a>
            <a class="edit-me nuke-cookie" href="javascript:void(0)" title="nuke cookies">nuke</a>
        </div>
    <?php } ?>

    <div class="quick-header">

        <h1>Quick Links</h1>
        <ul>
            <li id="help-tab"><a class="edit-me show-help" href="javascript:void(0)">Help</a></li>
            <li id="user-tab"><a class="edit-me login-form-load" href="javascript:void(0)">Load Saved Links</a></li>
        </ul>

        <!-- USER -->
        <div class="quick-user">
            <!-- LOGGED IN -->
            <div class="user-identified hidden">
                <?php __( 'Welcome' ) ?> <span class="user-name"></span>.
                <a class="edit-me logout" href="javascript:void(0)">Logout</a>
            </div>

            <!-- NEW TO QUICK ACCESS -->
            <div class="user-new hidden">
                <a class="edit-me dismiss-help" href="javascript:void(0)">Hide help text</a>
                <h4>Welcome to the Quick Links customization interface </h4>
                <p>You may add, remove or organize the links in your Quick Links drop-down menu.
                    All changes are saved immediately by your current web browser. To save changes
                    permanently and make them available on any computer, you'll need to log in with
                    your Williams user id and password.
                    <a class="edit-me" target="_new" href="http://wordpress.williams.edu/quick-links/">Read full
                        documentation &raquo;</a>
                </p>
            </div>
        </div>

    </div>

    <!-- FORM TEMPLATE -->
    <form id="custom-item-form-template" class="custom-item-form hidden">
        <div class="form-item">
            <label for="custom-item-title">Title</label>
            <input type="text" name="custom-item-title" class="custom-item-title" value="">
        </div>
        <div class="form-item">
            <label for="custom-item-url">URL</label>
            <input type="text" class="custom-item-url" name="custom-item-url" value="">
        </div>
        <input type="submit" class="button" value="Add">
        <a class="cancel-edit hidden" href="javascript:void(0)">Cancel</a>
    </form>

    <div class="quick-content">

        <!-- YOUR LINKS -->
        <div class="col-wrapper right-col" id="preview-links">
            <div class="above-list">
                <a class="restore-default-links" href="javascript:void(0)">restore default links</a>
                <h3>Your Links</h3>
                <div class="quick-tools">
                    <p>Add
                        <a class="edit-me quick-tool" href="javascript:void(0)" data-tool="college-links">Williams Links</a>
                        <a class="edit-me quick-tool" href="javascript:void(0)" data-tool="custom-link">Custom Link</a>
                        <a class="edit-me quick-tool" href="javascript:void(0)" data-tool="custom-cat">Divider</a>
                        <a class="edit-me quick-tool" href="javascript:void(0)" data-tool="quick-save"
                           title="Save links across all browsers.">
                            <span class="bts bt-upload"></span>
                        </a>
                    </p>
                </div>
            </div>
            <ul id="your-links"></ul>
        </div>

        <!-- COLLEGE LINKS -->
        <div class="col-wrapper left-col" id="college-links">
            <div class="above-list">
                <h3>Suggested Williams Links</h3>
                <p> Drag links from this column to the Your Links column.</p>
                <input class="filter" type="text" name="quick-filter-input" id="quick-filter-input"/>
            </div>
            <ul id="link-options">
                <?php
                $data = $meerkat_ql->load_all_links();
                foreach( $data as $link => $info ){
                    echo '<li><span class="quick-item">' . $info[ 'title' ] . '</a></span>';
                    echo '<a target="_new" class="link-goes-to" href="' . $info[ 'url' ] . '">';
                    echo '<div class="sprite icon-16 external-link"></div></a></li>';
                }
                ?>
            </ul>
        </div>

        <!-- CUSTOM LINK -->
        <div class="col-wrapper left-col hidden" id="custom-link">
            <h3>Add Custom Link</h3>
            <p> Insert any URL and a descriptive title. Click 'go' and it will be added to your quick links. </p>
            <div class="custom-item-form-goes-here"></div>
        </div>

        <!-- CUSTOM CAT -->
        <div class="col-wrapper left-col hidden" id="custom-cat">
            <h3>Add Divider</h3>
            <p> Add a section divider to help you organize your links. </p>
            <div class="custom-item-form-goes-here cat-only"></div>
        </div>

        <!-- LOGIN -->
        <div class="col-wrapper left-col hidden" id="login-form">
            <h3>Login</h3>
            <div class="login-instr-load quick-instr hidden">
                Any changes you've made have already been saved to your current web browser.
                Additionally, if you log in to your Williams account, you can save and access your lins from any
                computer or web browser.
            </div>
            <div class="quick-instr login-hint">Please log in with your Williams username and password.</div>
            <div class="login-status hidden">Login failed. Please try again.</div>
            <form id="ldap-login" name="login" action="<?php echo SECURE_THEME_URL; ?>lib/auth.php" method="POST">
                <div class="form-item">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" size="16" maxlength="48"/>
                </div>
                <div class="form-item">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" size="16" maxlength="48"/>
                </div>
                <input type="hidden" id="fx" name="fx" value="">
                <input type="submit" class="button" value="Log In"/>
                <a class="quick-tool" href="javascript:void(0)" tool="college-links">Cancel</a>
            </form>
        </div>

    </div><!-- end .quick-content -->