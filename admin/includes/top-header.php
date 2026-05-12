<div class="header-dashboard">
    <div class="wrap">
        <div class="header-left">
            <a href="./">
                <img
                    class=""
                    id="logo_header_mobile"
                    alt=""
                    src="images/logo.png"
                    data-light="images/logo.png"
                    data-dark="images/logo-dark.png"
                    data-width="154px"
                    data-height="52px"
                    data-retina="images/logo/logo@2x.png"
                />
            </a>
            <div class="button-show-hide">
                <i class="icon-menu-left"></i>
            </div>
            <form class="form-search flex-grow">
                <fieldset class="name">
                    <input
                        type="text"
                        placeholder="Search here..."
                        class="show-search"
                        name="name"
                        tabindex="2"
                        value=""
                        aria-required="true"
                        required=""
                    />
                </fieldset>
                <div class="button-submit">
                    <button class="" type="submit"><i class="icon-search"></i></button>
                </div>
            </form>
        </div>

        <div class="header-grid">
            <div class="header-item button-dark-light">
                <i class="icon-moon"></i>
            </div>

            <div class="header-item button-zoom-maximize">
                <div class="">
                    <i class="icon-maximize"></i>
                </div>
            </div>

            <div class="popup-wrap user type-header">
                <div class="dropdown">
                    <button
                        class="btn btn-secondary dropdown-toggle"
                        type="button"
                        id="dropdownMenuButton3"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <span class="header-user wg-user">
                            <span class="image">
                                <img src="images/user.png" alt="" />
                            </span>
                            <span class="flex flex-column">
                                <span class="body-title mb-2"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                                <span class="text-tiny">Admin</span>
                            </span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3">
                        <li>
                            <a href="account.php" class="user-item">
                                <div class="icon">
                                    <i class="icon-user"></i>
                                </div>
                                <div class="body-title-2">Account</div>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="user-item">
                                <div class="icon">
                                    <i class="icon-log-out"></i>
                                </div>
                                <div class="body-title-2">Log out</div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
