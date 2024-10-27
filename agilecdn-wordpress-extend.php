<?php
/**
 * Plugin Name: AgileCDN
 * Plugin URI: 
 * Description: Use AgileCDN to speed up and secure your web services
 * Version: 1.0
 * Author: AgileCDN
 * Author URI: 
 * License: GPLv3
 */

require_once(plugin_dir_path(__FILE__).'/config.php' );
require_once(plugin_dir_path(__FILE__).'inc/agile_cdn_rewrite.class.php' );

function agile_main_page() {
    ?>
    <div id="agile-cdn">
        <div class="header">
            <img src="<?php echo esc_url( plugins_url( 'img/logo-light.png', __FILE__ ) ); ?>" alt="AgileCDN">
            <a href="<?php echo esc_attr(AGILEWING_CDN.'#/cdn/config/list') ?>" target="_new" class="header-link finger">
                <span>You can use more AgileCDN's funtionality through AgileCDN dashboard</span>
                <img src="<?php echo esc_url( plugins_url( 'img/arrow-right.png', __FILE__ ) ); ?>" alt="AgileCDN">
            </a>
        </div>
        <div class="body">
            <span id="agile-cdn-notice">
                <?php 
                settings_errors('agile_cdn_url');
                ?>
            </span>
            <form id="agile-cdn-form" method="post" action="options.php">
                <?php settings_fields( 'agile-cdn-settings' ); ?>
                <?php do_settings_sections( 'agile-cdn-settings' ); ?>
                <h1 class="title">Setting</h1>
                <div class="form-item">
                    <div class="label">
                        Current Plan
                    </div>
                    <div class="input-inline">
                        Pay As You Go
                        <a href="<?php echo esc_attr(AGILEWING_CDN.'#/billing/wallet/list?show=true') ?>" target="_new" class="link finger">
                            Top up
                        </a>
                        <span class="inline-tip">New users are offered a 7 day trial and pay on demand after 7 days</span>
                    </div>
                </div>
                <div class="form-item">
                    <div class="label">
                        Site URL
                        <div class="tooltip">
                            <img src="<?php echo esc_url( plugins_url( 'img/question.png', __FILE__ ) ); ?>" alt="AgileCDN">
                            <span class="tooltip-text" style="top:-20px;">This is the source domain name of the website</span>
                        </div>
                    </div>
                    <div class="input">
                        <span class="input-inline">
                            <input type="text" name="agile_cdn_url" value="<?php echo (empty(esc_attr(get_option('agile_cdn_url'))) ? get_site_url() : esc_attr(get_option('agile_cdn_url'))); ?> ">
                            <div class="input-validate"></div>
                        </span>
                    </div>
                </div>
                <div class="form-item">
                    <div class="label">
                        Static Files's CDN domain
                        <div class="tooltip">
                            <img src="<?php echo esc_url( plugins_url( 'img/question.png', __FILE__ ) ); ?>" alt="AgileCDN">
                            <span class="tooltip-text" style="top:-18px;">Don’t Have The Prefix? Register</span>
                        </div>
                    </div>
                    <div class="input">
                        <span class="input-inline">
                            <input type="text" placeholder="Paste your CDN domain(custom domain) from AgileCDN beckend" name="agile_cdn_prefix" value="<?php echo (empty(esc_attr(get_option('agile_cdn_prefix'))) ? '' : esc_attr(get_option('agile_cdn_prefix'))); ?>">
                            <div class="input-validate"></div>
                        </span>
                        <a href="<?php 
                            $site_url = esc_attr(get_option('agile_cdn_url'));
                            if(filter_var($site_url, FILTER_VALIDATE_URL)) {
                                $site_url = wp_parse_url($site_url);
                                $site_url_path = (array_key_exists('path', $site_url) ? $site_url["path"] : null);
                                echo esc_attr( AGILEWING_WEBSITE.'register/?sourceType=wordpress&domain='.$site_url["host"].$site_url_path );
                            } else {
                                echo esc_attr( AGILEWING_WEBSITE.'register/?sourceType=wordpress' );
                            }
                        ?>" target="_new" class="link finger">
                            Prefix -> CDN Domain
                        </a>
                    </div>
                </div>
                <div class="form-item">
                    <div class="label">
                        Enable AgileCDN
                    </div>
                    <div class="input">
                        <label class="switch finger" style="margin-right: 18px;">
                            <?php
                                if (esc_attr( get_option('agile_cdn_enabled') ) == 'on') {
                                    $enabled_checked = "checked";
                                } else {
                                    $enabled_checked = "";
                                }
                            ?>
                            <input type="checkbox" name="agile_cdn_enabled" <?php echo esc_attr($enabled_checked); ?> >
                            <div class="slider round"></div>
                        </label>
                        <a href="https://www.agilecdn.cloud/tips/implementation-instructions-agilecdn-plugin/" target="_new" class="link finger">
                            How to implement AgileCDN？
                        </a>
                    </div>
                </div>
                <div class="form-item footer">
                    <span class="dot-group">
                        <button id="agliecdn-submit" type="submit" class="finger">Save</button>
                        <span id="agliecdn-submit-botton-dot" class=""></span>
                    </span>
                    
                </div>
            </form>

            <div class="divider"></div>

            <h1 class="title">Purge</h1>
            <p class="content">Purging is a command to the CDN to stop serving a file from cache.For instance, by making a
                purge for
                /images/screen.png, you instruct the network to invalidate this cached picture on all CDN servers
                globally .When the purge is finished and a visitor requests the purged file, the network will make a
                conditional request to the main server.Then the origin server will respond by delivering the newer file
                versions which edge serve will cache and serve to visitors.</p>
            <div class="form-item footer">
                <a href="<?php echo esc_attr(AGILEWING_CDN.'#/cdn/monitor/invalidation/list') ?>" target="_new">
                    <button type="submit" class="finger">Cache Purge</button>
                </a>
            </div>
            <div id="agile-cdn-loading" class="mask">
                <div class="loading"></div>
            </div>
        </div>
        <div class="message hidden" id="agile-cdn-tip">
            <div class="message-notice center">
                <div class="message-notice-content">
                    <span class="message-notice-title">
                        <img src="<?php echo esc_url( plugins_url( 'img/right.png', __FILE__ ) ); ?>" alt="AgileCDN"> Save Success!
                    </span>
                </div>
                <span id="agile-cdn-tip-close" class="message-notice-x">
                    <img src="<?php echo esc_url( plugins_url( 'img/error.png', __FILE__ ) ); ?>" alt="AgileCDN">
                </span>
            </div>
        </div>
    </div>
    <?php
}
