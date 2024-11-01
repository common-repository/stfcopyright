<?php
/*
  Plugin Name: stfCopyright
  Plugin URI: https://www.studio-fun.net/stfcopyright
  Description: Wordpress content copyright tag creation plugin
  Author: studio-fun.net k@n
  Version: 1.1
  Author URI: https://www.studio-fun.net/
  License: GPLv2
  Text Domain: stfCopyright
  Domain Path: /languages
 */

class stf_Copyright {
    const DOMAIN = 'stfCopyright';
    /**
     * [__construct description]
     */
    function __construct() {
        //languagesfile
        load_plugin_textdomain(self::DOMAIN, false, basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);

        //タイムゾーン設定（wordpressのタイムゾーンを設定）
        $this_timezone = get_option('timezone_string');
        date_default_timezone_set($this_timezone);

        //管理画面から起動されたときのみ
        add_action('admin_menu', array($this, 'sft_add_sub_menu'));

        //プラグイン有効時の処理
        if (function_exists('register_activation_hook')) {
            register_activation_hook(__FILE__, array($this, 'sft_option_setting'));
        }

        //プラグイン無効時の処理
        if (function_exists('register_deactivation_hook')) {
            //無効時は何もしない（DB設定は残す）
            //register_deactivation_hook(__FILE__, array($this, 'studiofun_net_option_setting_del'));
        }

        //プラグインアンインストール時の処理
        if (function_exists('register_uninstall_hook')) {
            register_uninstall_hook(__FILE__, 'studiofun_net_option_setting_del');
        }
    }

    /**
     * DB項目追加
     * [sft_option_setting description]
     * @return [type] [description]
     */
    function sft_option_setting() {
        //登録設定項目を追加
        if (!get_option('stf_tools')) {
            $setDate = date("Y");
            add_option('stf_tools', 1);
            add_option('stf_tools_iyear', $setDate, '', 'yes');
            add_option('stf_tools_uyear', '', '', 'yes');
            add_option('stf_tools_aoutupdate', '1', '', 'yes');
            add_option('stf_tools_owner', '', '', 'yes');
            add_option('stf_tools_type', '1', '', 'yes');
            add_option('stf_tools_freetext', '', '', 'yes');
        }
    }

    /**
     * 管理画面表示設定
     * [[Description]]
     */
    function sft_add_sub_menu() {
        add_submenu_page('options-general.php', 'stfCopyright', 'stfCopyright', 'manage_options', 'stfcp', array($this, 'sft_show_text_option_page'));
    }

    /**
     * 管理画面用Script読み込み
     * [stf_copyright_admin_script description]
     * @return [type] [description]
     */
    function stf_copyright_admin_script() {
        echo <<<EOD
		<script language='javascript' type='text/javascript'>
		
        jQuery(document).ready(function() {
	        //ドキュメント読み込み後実行
	        if (jQuery('#stf_tools_aoutupdate').prop('checked')) {
	            jQuery('#stf_tools_uyear').attr('disabled', 'disabled');
	        } else {
	            jQuery('#stf_tools_uyear').removeAttr('disabled');
	        }
	        
			var type_val = jQuery('[name=stf_tools_type]:checked').val();
			if (type_val === '4') {
				jQuery('#stf_tools_uyear').attr('disabled', 'disabled');
                jQuery('#stf_tools_freetext').attr('disabled', 'disabled');
                jQuery('#stf_tools_owner').removeAttr('disabled');
                jQuery('#stf_tools_iyear').removeAttr('disabled');                
            }else if (type_val === '5') {
                jQuery('#stf_tools_freetext').removeAttr('disabled');
				jQuery('#stf_tools_owner').attr('disabled', 'disabled');
                jQuery('#stf_tools_iyear').attr('disabled', 'disabled');
                jQuery('#stf_tools_uyear').attr('disabled', 'disabled');
			} else {
				if (!jQuery('#stf_tools_aoutupdate').prop('checked')) {
					jQuery('#stf_tools_uyear').removeAttr('disabled');
				}
                jQuery('#stf_tools_freetext').attr('disabled', 'disabled');
			}

            jQuery('#stf_tools_aoutupdate').click(function() {
                if (jQuery(this).prop('checked') === true) {
                    jQuery('#stf_tools_uyear').attr('disabled', 'disabled');
                } else {
                    jQuery('#stf_tools_uyear').removeAttr('disabled');
                }
            });

			jQuery('[name=stf_tools_type]').click(function() {
				if(jQuery('[name=stf_tools_type]:checked').val()==='4'){
                    jQuery('#stf_tools_uyear').attr('disabled', 'disabled');
                    jQuery('#stf_tools_freetext').attr('disabled', 'disabled');
                    jQuery('#stf_tools_owner').removeAttr('disabled');
                    jQuery('#stf_tools_iyear').removeAttr('disabled');
				} else if(jQuery('[name=stf_tools_type]:checked').val()==='5'){
                    jQuery('#stf_tools_freetext').removeAttr('disabled');
                    jQuery('#stf_tools_owner').attr('disabled', 'disabled');
                    jQuery('#stf_tools_iyear').attr('disabled', 'disabled');
                    jQuery('#stf_tools_uyear').attr('disabled', 'disabled');                 
				} else {
					if(!jQuery('#stf_tools_aoutupdate').prop('checked')) {
						jQuery('#stf_tools_uyear').removeAttr('disabled');
					}
                    jQuery('#stf_tools_owner').removeAttr('disabled');
                    jQuery('#stf_tools_iyear').removeAttr('disabled');
                    jQuery('#stf_tools_freetext').attr('disabled', 'disabled');
				}
            });
        });
		</script>
EOD;
    }

    /**
     * オブション画面に表示する内容
     * [sft_show_text_option_page description]
     * @return [type] [description]
     */
    function sft_show_text_option_page() {
        //登録ボタン押下でポストされた場合、データ保存 filter_input( INPUT_POST, "bbb" );
        if (filter_input(INPUT_POST, 'posted') === 'data_save') {
            //設定画面で入力された設定値を保存
            $stf_tools_iyear = filter_input(INPUT_POST, 'stf_tools_iyear');
            $stf_tools_uyear = filter_input(INPUT_POST, 'stf_tools_uyear');
            $stf_tools_owner = filter_input(INPUT_POST, 'stf_tools_owner');
            $stf_tools_type = filter_input(INPUT_POST, 'stf_tools_type');
            $stf_tools_freetext = filter_input(INPUT_POST, 'stf_tools_freetext');

            update_option('stf_tools_iyear', $stf_tools_iyear);
            update_option('stf_tools_uyear', $stf_tools_uyear);
            update_option('stf_tools_owner', $stf_tools_owner);
            update_option('stf_tools_type', $stf_tools_type);
            update_option('stf_tools_freetext', $stf_tools_freetext);
            // チェックボックスはチェックされないとキーも受け取れないので、ない時は0にする
            if (filter_input(INPUT_POST, 'stf_tools_aoutupdate') === null) {
                $chkbox_aoutupdate = 0;
            } else {
                $chkbox_aoutupdate = 1;
            }
            update_option('stf_tools_aoutupdate', $chkbox_aoutupdate);
        }

        //jQueryScript
        $this->stf_copyright_admin_script();

        // 更新完了を通知
        if (filter_input(INPUT_POST, 'posted') !== null) {
            $saved = __('It saved', self::DOMAIN);
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>' . $saved . '</strong></p></div>'; //設定を保存しました。
        }
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br></div>
            <h2><?php _e('stfCopyright setting', self::DOMAIN); ?></h2>
            <div id="welcome-panel" class="welcome-panel">
                <h3><span class="dashicons dashicons-lightbulb"></span><?php _e('CopyrightMaker', self::DOMAIN); ?></h3>
                <p><?php _e('stfCopyright is a plug-in that automatically generates Copyright to be displayed on the site footer etc. The update year can also be updated automatically', self::DOMAIN); ?></p>
                <p><?php _e('Please paste short code below on WordPress theme (footer.php etc.). ', self::DOMAIN); ?><br>
                    <input type="text" onfocus="this.select();" readonly="readonly" value="&lt;?php echo do_shortcode('[stfcopyright]'); ?&gt;" class="large-text code">
                </p>
            </div>
            <hr>
            <h3><?php _e('Copyright display setting', self::DOMAIN); ?></h3>
            <form method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="stf_tools_owner"><?php _e('Name of copyright owner', self::DOMAIN); ?></label>
                        </th>
                        <td><input name="stf_tools_owner" type="text" id="stf_tools_owner" class="regular-text" value="<?php echo get_option('stf_tools_owner'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="stf_tools_iyear"><?php _e('Copyright Issued Year', self::DOMAIN); ?></label>
                        </th>
                        <td>
                            <input name="stf_tools_iyear" type="number" max="9999" min="1" id="stf_tools_iyear" value="<?php echo get_option('stf_tools_iyear'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Update year auto update', self::DOMAIN); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Update year auto update', self::DOMAIN); ?></span></legend><label for="stf_tools_aoutupdate">
                                    <input name="stf_tools_aoutupdate" type="checkbox" id="stf_tools_aoutupdate" value="1" <?php checked(1, get_option('stf_tools_aoutupdate')); ?>>
                                    <?php _e('Automatically renew the copyright renewal year at the New Year (1/1).', self::DOMAIN); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="stf_tools_uyear"><?php _e('Copyright renewal year (manual)', self::DOMAIN); ?></label>
                        </th>
                        <td>
                            <input name="stf_tools_uyear" type="number" max="9999" min="1" id="stf_tools_uyear" value="<?php echo get_option('stf_tools_uyear'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="inputtext"><?php _e('Copyrights format', self::DOMAIN); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Date format', self::DOMAIN); ?></span></legend>
                                <label>
                                    <input type="radio" name="stf_tools_type" class="stf_tools_type" value="1" <?php checked(1, get_option('stf_tools_type')); ?> > 
                                    <span class="date-time-text format-i18n">© 2010 - 2015 [copyright owner] All Rights Reserved. <?php _e('(Copyright Issuing Year - Updated Year)', self::DOMAIN); ?></span>
                                </label>
                                <br>
                                <label><input type="radio" name="stf_tools_type" class="stf_tools_type" value="2" <?php checked(2, get_option('stf_tools_type')); ?> > 
                                    <span class="date-time-text format-i18n">Copyright © 2010-2015 [copyright owner] All Rights Reserved.  <?php _e('(Copyright Issuing Year - Updated Year)', self::DOMAIN); ?></span></label>
                                <br>
                                <label><input type="radio" name="stf_tools_type" class="stf_tools_type" value="3" <?php checked(3, get_option('stf_tools_type')); ?> >
                                    <span class="date-time-text format-i18n">Copyright © 2015 [copyright owner] All Rights Reserved. <?php _e('(Update year)', self::DOMAIN); ?></span></label>
                                <br>
                                <label><input type="radio" name="stf_tools_type" class="stf_tools_type" value="4" <?php checked(4, get_option('stf_tools_type')); ?>> 
                                    <span class="date-time-text format-i18n">© 2010 [copyright owner] <?php _e('(Copyright Issuing Year)', self::DOMAIN); ?></span></label><br>
                                <label><input type="radio" name="stf_tools_type" class="stf_tools_type" value="5" <?php checked(5, get_option('stf_tools_type')); ?> >
                                    <span class="format-i18n"><?php _e('Free entry of Copyright', self::DOMAIN); ?></span></label>                                   
                                <input id="stf_tools_freetext" name="stf_tools_freetext" type="text" onfocus="this.select();" value="<?php echo get_option('stf_tools_freetext'); ?>" placeholder="<?php _e('Free entry of Copyright', self::DOMAIN); ?>" class="large-text">
                                <br>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="stf_tools_shortcode"><?php _e('Short code', self::DOMAIN); ?></label>
                        </th>
                        <td class="shortcode column-shortcode" data-colname="Short code">
                            <span class="shortcode">
                                <input type="text" onfocus="this.select();" readonly="readonly"
                                       value="&lt;?php echo do_shortcode('[stfcopyright]'); ?&gt;" class="large-text code">
                            </span>
                        </td>
                    </tr>
                </table>
                <hr>
                <input type="hidden" name="posted" value="data_save">
                <?php submit_button(); // 送信ボタン   ?>
            </form>
        </div>
        <?php
    }
}

//stf_Copyright インスタンス化
$stf_Copyright = new stf_Copyright;

/**
 * ショートコード登録
 */
add_shortcode('stfcopyright', 'studiofun_net_copyright');

/**
 * コピーライト出力
 * [stf_copyright description]
 * @return [type] [description]
 */
function studiofun_net_copyright() {
    $copyright_str = "";
    $stf_tools_iyear = get_option('stf_tools_iyear');
    $stf_tools_uyear = get_option('stf_tools_uyear');
    $stf_tools_owner = get_option('stf_tools_owner');
    $stf_tools_aoutupdate = get_option('stf_tools_aoutupdate');
    $stf_tools_type = get_option('stf_tools_type');
    $stf_tools_freetext = get_option('stf_tools_freetext');

    if ($stf_tools_aoutupdate === '1') {
        $stf_tools_uyear = date("Y");
    }

    switch ($stf_tools_type) {
        case 1:
            $copyright_str = "&#169; " . $stf_tools_iyear . " - " . $stf_tools_uyear . " " . $stf_tools_owner . " All Rights Reserved.";
            break;
        case 2:
            $copyright_str = "Copyright &#169; " . $stf_tools_iyear . " - " . $stf_tools_uyear . " " . $stf_tools_owner . " All Rights Reserved.";
            break;
        case 3:
            $copyright_str = "Copyright &#169; " . $stf_tools_uyear . " " . $stf_tools_owner . " All Rights Reserved.";
            break;
        case 4:
            $copyright_str = "&#169; " . $stf_tools_iyear . " " . $stf_tools_owner;
            break;
        case 5:
            $copyright_str = $stf_tools_freetext;
            break;
    }
    return $copyright_str;
}

/**
 * プラグインアンインストール時に呼び出される処理
 * @return [type] [description]
 */
function studiofun_net_option_setting_del() {
    //登録設定項目を追加()
    delete_option('stf_tools');
    delete_option('stf_tools_iyear');
    delete_option('stf_tools_uyear');
    delete_option('stf_tools_aoutupdate');
    delete_option('stf_tools_owner');
    delete_option('stf_tools_type');
    delete_option('stf_tools_freetext');
}
