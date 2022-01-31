<?php
/* 
Purpose: Quick Link Editor (shadowbox)
*/
?>
<div id="quicklinks">
    <div class="quick-header">
        <ul id="quick-utility">
            <li id="user-tab">
                <button class="btn quick-tool" data-tool="show" data-target="#quick-login">Log in</button>
            </li>
            <li>
                <button class="btn quick-tool" data-tool="restore-default-links">Restore Default Links</button>
            </li>
            <li>
                <button class="btn quick-tool" data-tool="show" data-target="#quick-help">Help</button>
            </li>
            <?php if (is_super_admin()) { ?>
                <li>
                    <button class="btn quick-tool" data-tool="show-cookies">Show Cookies</button>
                </li>
                <li>
                    <button class="btn quick-tool" data-tool="delete-cookies">Delete Cookies</button>
                </li>
            <?php } ?>
        </ul>
    </div>

    <div class="quick-content">
        <div class="col-wrapper left-col">
            <div class="col-header">
                <h4 class="col-title">Add Links</h4>
                <div class="quick-status"></div>
            </div>

            <?php echo Quicklinks::instance()->getEditorTabs() ?>
            <p class="feedback right"><small><a target="_blank" href="https://communications.williams.edu/web-development/quick-links-feedback/">Feedback</a></small></p>
        </div>

        <?php // YOUR LINKS ?>
        <div class="col-wrapper right-col" id="preview-links">
            <div class="col-header">
                <h4 class="col-title">Your Quick Links</h4>
                <i class="quick-user-icon btb bt-user" title=""></i>
            </div>
            <ul id="your-links" class="dismissible-container"><?php // generated ql goes here. ?></ul>
        </div>
    </div><!-- end .quick-content -->

    <div class="dismissible">
        <?php // LOGGED IN ?>
        <div id="quick-login">
            <button class="bts bt-times quick-tool" data-tool="dismiss" title="Hide login form"></button>
            <p>Any changes you've made have already been saved to your current web browser. Additionally, if you log in to your Williams account, you can save and access your links from any computer or web browser.</p>
            <p>Please log in with your Williams username and password.</p>
            <div class="quick-status"></div>
            <form id="quick-login-form" name="login">
                <div class="form-item">
                    <label for="username">User</label>
                    <input type="text" name="username" id="username" size="16" maxlength="48">
                </div>
                <div class="form-item">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" size="16" maxlength="48">
                </div>
                <div class="form-item">
                    <label for="get" title="Overwrite current Quick Links">Retrieve Links</label><input checked type="radio" name="method" value="get">
                </div>
                <div class="form-item">
                    <label for="put" title="Overwrite saved Quick Links">Save Links</label><input type="radio" name="method" value="put">
                </div>
                <div class="buttons">
                    <button class="btn quick-tool" data-tool="login">Log In</button>
                </div>
            </form>
        </div>

        <?php //  NEW TO QUICK ACCESS ?>
        <div id="quick-help">
            <button class="bts bt-times quick-tool" data-tool="dismiss" title="Hide help text"></button>
            <h4>Welcome to the Quick Links customization interface </h4>
            <p>You may add, remove or organize the links in your Quick Links drop-down menu.</p>
            <p>Choose from common Williams links, create your own custom links or add dividers for organization.</p>
            <p>All changes are saved immediately by your current web browser.
            To save changes permanently and make them available on any computer, you'll need to log in with your Williams user id and password. <a target="_new" href="http://wordpress.williams.edu/quick-links/">Read full documentation</a></p>
        </div>
    </div>

</div>