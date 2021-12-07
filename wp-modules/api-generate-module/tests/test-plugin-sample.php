<?php
/**
 * Class SampleTest
 *
 * @package Plugin_Sample
 */


//require_once '../SmoketestHelper.php';
//require_once __DIR__ . '/../../../../../ec2/InstallPermissionsK8s.php';

/**
 * @group WordPress
 */
class WPEngineSystemPluginTest extends WP_UnitTestCase
{

    public function setUp(): void
    {
        // set current user to wpengine
        $this->assertTrue(SmoketestHelper::wpSetCurrentUser(), "Failed to set current user");

        // server protocol set by browser
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

        // clear requests
        $_REQUEST = array();

        // set nonce
        $_REQUEST['_wpnonce'] = wp_create_nonce(PWP_NAME . '-config');

        $_REQUEST['nonce'] = wp_create_nonce('wpe_common_ajax_nonce');

    }

    /**
     * Test the WP Engine Plugin "Purge All Caches" button
     * https://wpengine.atlassian.net/browse/PHP-131
     *
     * NOTE: The plugin performs three actions when this button is pressed and does not check return values for these
     * functions.
     *     WpeCommon::purge_memcached();
     *         Testable by checking memcache.
     *     WpeCommon::clear_maxcdn_cache();
     *         Relies on api call, which puts site in queue to be purged. Just checking for no errors.
     *     WpeCommon::purge_varnish_cache();
     *         Just checking for no errors on purging full blog.
     */
    public function testPurgeAll()
    {
        $_REQUEST['tab'] = 'caching';

        // set request
        $_REQUEST['purge-all'] = true;
        $this->assertTrue(wpe_param('purge-all'), "Failed to set request");

        // dirty memcache
        $key = 'wpe_dummy_key';
        $value = 'wpe_dummy_value';
        wp_cache_set($key, $value);

        // assert memcache is dirty using force flag
        $cache_get_value = wp_cache_get($key, '');
        $this->assertEquals($value, $cache_get_value, "Could not assert memcache is dirty.");

        // load the admin-ui file
        $common = new \WpeCommon();

        // capture plugin html output
        ob_start();
        $common->wpe_admin_page();
        $output = ob_get_clean();

        // assert that admin-ui file was included
        //$admin_ui_file = $this->doc_root. '/wp-content/mu-plugins/wpengine-common/admin-ui.php';
        //$this->assertTrue(in_array($admin_ui_file, get_included_files()), "Expected file not included: $admin_ui_file");

        // look for message that button was pressed
        $message = "The caches have been cleared";
        $this->assertTrue(!!strpos($output, $message), "Message not found: $message");

        // verify clean memcache
        $cache_get_value = wp_cache_get($key, '');
        $this->assertFalse($cache_get_value, "Could not assert memcache has been purged.");
    }

    /**
     * Test the WP Engine Plugin "Reset File Perms" button
     * https://wpengine.atlassian.net/browse/PHP-132
     *
     * This test puts the file perms for one core file into a bad state,
     * hits the button in the plugin, and then verifies the file was set back to the correct owner.
     *
     * On the vagrant, file perms are not modifiable in the default configuration. Changes made to file owner are
     * skipped on the vagrant, and this test only checks that the HTML returned from plugin is correct and that the call
     * to the api does not fail.
     */
    public function testResetFilePerms()
    {
        if (SmoketestHelper::isK8s()) {
            $this->markTestSkipped('EVLV-1170: skipping flaky test');
        }

        // set tab
        $_REQUEST['tab'] = 'site-settings';
        $this->assertSame(wpe_param('tab'), 'site-settings', 'Failed to set tab');

        // set request
        $_REQUEST['file-perms'] = true;
        $this->assertTrue(wpe_param('file-perms'), "Failed to set request");
        
        // set nonce
        $_REQUEST['_wpnonce'] = wp_create_nonce(PWP_NAME . '-site-settings-file-perm-reset');

        // the owner of this file should be www-data
        $core_file = $this->doc_root . '/wp-login.php';

        // if this is not a vagrant, put file perms in a bad state
        if (! SmoketestHelper::isVagrant()) {
            // put perms in a bad state
            $user_name = 'nobody';
            $this->assertTrue(chown($core_file, $user_name), "Unable to change owner of file: $core_file");

            // assert file is in bad state
            $owner = posix_getpwuid(fileowner($core_file));
            $this->assertEquals($owner['name'], $user_name, "Unable to change owner of file: $core_file");
        }

        // load the admin-ui file
        $common = new \WpeCommon();

        // capture plugin html output
        ob_start();
        $common->wpe_admin_page();
        $output = ob_get_clean();

        // look for message that button was pressed
        $message = "Command has been started. This can take a while, please be patient.";
        $this->assertTrue(!!strpos($output, $message), "Message not found: $message");

        // if this is not a vagrant, assert that file perms were fixed
        if (!SmoketestHelper::isVagrant()) {
            if (SmoketestHelper::isK8s()) {
                $perms = new InstallPermissionsK8s();
                $rel_core_file_path = substr($core_file, strlen('/nas/content/live'));
                $expected_owner = $perms->getOwner($rel_core_file_path);
            } else {
                $expected_owner = 'www-data';
            }

            // Crude retry logic to give the plugin a few seconds to complete
            // context in https://wpengine.atlassian.net/browse/QE-3424
            // there is still a race here; the test fails without the retries
            for ($x=0; $x<=120; $x++) {
                // Because stat cache doesn't update the info about the file in subsequent getpwuid() calls, clear cache.
                clearstatcache(true, $core_file);
                $owner = posix_getpwuid(fileowner($core_file));
                if ($expected_owner === $owner['name']) {
                    break;
                }
                sleep(1);
            }

            $this->assertEquals($expected_owner, $owner['name'], "Incorrect perms on $core_file");
        }
    }

    /**
     * Test that the plugin calculates correct values compared to info in server-meta.
     * https://wpengine.atlassian.net/browse/PHP-149
     *
     * Verify fields
     *  - name
     *  - public ip
     *  - sftp ip
     */
    public function testPluginGetInfo()
    {
        // get site info from server-meta
        $sm_site_info = $this->getSiteInfo();

        // get site info from plugin
        $plugin = new \WpeCommon();
        $plugin_site_info = $plugin->get_site_info();

        // assert values are correct
        $this->assertEquals($sm_site_info['name'], $plugin_site_info->name, "Plugin site name does not match server meta.");

// Disable because of AWS condition        $this->assertEquals($sm_site_info['ip'], $plugin_site_info->public_ip);
        if (! SmoketestHelper::isK8s()) {
            // EVLV-39: k8s utility does not handle sftp
            $this->assertEquals($sm_site_info['sftp_ip'], $plugin_site_info->sftp_ip, "Plugin sftp info does not match server meta.");
        } else {
            $this->assertEquals($sm_site_info['sftp_endpoint'], $plugin_site_info->sftp_endpoint, "Plugin endpoint does not match server meta.");
        }
    }

    /**
     * Test that the output generated in plugin General Settings is correct
     * https://wpengine.atlassian.net/browse/PHP-149
     *
     * Verify fields
     *  - name
     *  - public ip
     *  - sftp host name
     *  - sftp ip
     *  - sftp port
     */
    public function testGeneralSettingsOutput()
    {
        // get site info from plugin
        $plugin = new \WpeCommon();
        $plugin_info = $plugin->get_site_info();

        // capture admin page
        ob_start();
        $plugin->wpe_admin_page();
        $output = ob_get_clean();

        // define expected output
// Disable because of AWS condition        $expected_output['cname']  = "CNAME: <code>{$plugin_info->name}.wpengine.com</code>";
// Disable because of AWS condition        $expected_output['aname'] =  "A record to <code class=\"wpe_public_ip\">{$plugin_info->public_ip}</code>";
        $expected_output['sftp_hostname'] = "(<i>not FTP!</i>) is at hostname <code>{$plugin_info->sftp_host}</code>";
        $expected_output['sftp_ip'] = "IP at <code class=\"wpe_sftp_ip\">{$plugin_info->sftp_ip}</code>";
        $expected_output['sftp_port'] = "on port <code>{$plugin_info->sftp_port}</code>";

        foreach ($expected_output as $expected) {
            $this->assertTrue(!!strpos($output, $expected), "Did not find output: $expected");
        }
    }

    /**
     * Test the WP Engine Plugin "Copy Live to Staging" button
     * https://wpengine.atlassian.net/browse/PHP-134
     *
     * This test gets the timestamp of the last staging snapshot, copies site to staging, and then checks the timestamp
     * again to assert that it has increased.
     */
    public function testCopyLiveToStaging()
    {
        if (SmoketestHelper::isK8s()) {
            $this->markTestSkipped('EVLV-39: skipping until k8s supports legacy staging');
        }

        $_REQUEST['snapshot'] = true;
        $_REQUEST['tab']      = 'staging';
        $this->assertTrue(wpe_param('snapshot'), "Failed to set request");

        // load the admin-ui file
        $common = new \WpeCommon();

        // remove last-mod
        $last_mod_location = $common::NAS_CONTENT . "/staging/" . PWP_NAME . "/". $common::STAGING_STATUS_FILE;
        if (file_exists($last_mod_location)) {
            unlink($last_mod_location);
        }

        // kick off request and suppress plugin html output
        ob_start();
        $common->wpe_admin_page();
        $output = ob_get_clean();

        // Keep track of staging push progress for test output
        $staging_progress = array();

        // see if staging site is ready
        $wait_interval = 10;  // secs
        $start_time = time();
        $time_limit = 90;  // secs
        while (($elapsed_time = time() - $start_time) < $time_limit) {
            sleep($wait_interval);
            clearstatcache();
            // verify file exists
            $last_mod_exists = file_exists($last_mod_location);
            // get the staging status
            $staging_status = $this->getStagingStatus();
            $is_ready = isset($staging_status['is_ready'])? $staging_status['is_ready']: false;
            // Append to staging_progress tracker
            $staging_progress[] = array(
                                    'test_elapsed_time' => $elapsed_time,
                                    'last_update' => $staging_status["last_update"],
                                    'status' => $staging_status["status"]
                                  );
            // check for success
            if ($last_mod_exists && $is_ready) {
                break;
            }
        }
        $staging_progress = json_encode($staging_progress, JSON_PRETTY_PRINT);
        print_r($staging_progress);
        // fail this test if either requirement not met
        $this->assertTrue($last_mod_exists, "Copy to staging timed out (FILE DOES NOT EXIST) after $elapsed_time seconds. Limit $time_limit seconds.");
        $this->assertTrue($is_ready, "Copy to staging timed out (NOT READY) after $elapsed_time seconds. Limit $time_limit seconds.");
    }

    /**
     * Test the WP Engine Plugin "Deploy from Staging to Live" button
     * https://wpengine.atlassian.net/browse/PHP-326
     *
     * This test places a test file in staging, then fakes an ajax call to kick off deploy. It checks a status file to
     * see when the deploy has completed, and then verifies that the test file is now in production.
     */
    public function testDeployStagingToLive()
    {
        if (SmoketestHelper::isK8s()) {
            $this->markTestSkipped('EVLV-39: skipping until k8s supports legacy staging');
        }

        // clear deploy status file contents
        $deploy_status_file = $this->doc_root ."/wpe-deploy-status-". SmoketestHelper::getDefaultInstall();
        $handle = fopen($deploy_status_file, "w+");
        fclose($handle);
        $this->assertEquals(filesize($deploy_status_file), 0, "Unable to clear deploy status file contents: $deploy_status_file");

        // place a test file in staging
        $test_file_name = "testDeployStagingToLive";
        $staging_test_file = "{$this->staging_doc_root}/{$test_file_name}";
        $this->assertTrue(touch($staging_test_file), "Unable to create test file in staging environment");

        // if test file exists in live, delete it
        $live_test_file = "{$this->doc_root}/{$test_file_name}";
        if (file_exists($live_test_file)) {
            unlink($live_test_file);
        }
        $this->assertFalse(file_exists($live_test_file), "Could not delete test file in live version of site");
        
        //$_REQUEST['tab'] = 'staging';
        //$common = new \WpeCommon();
        //$common->wpe_admin_page();

        // trigger the deploy which sends message to api
        ob_start();
        wpengine\admin_options\deploy_staging_to_live();
        ob_get_clean();

        $complete = false;

        // Watch deploy status for completion
        for ($i = 0; $i < 300; $i++) {
            // look for "Deploy Completed" in status file
            if (file_exists($deploy_status_file)) {
                $status_file_content = file_get_contents($deploy_status_file);
                if (strstr($status_file_content, "Deploy Completed")) {
                    $complete = true;
                    break;
                }
            }
            sleep(1);
        }

        // assert that the copy completed
        $this->assertTrue($complete);

        // assert that test file in staging is now in live
        // Make sure php isn't holding on to old data
        clearstatcache();
        // Sleep to make sure NFS is sync'd
        sleep(10);
        $this->assertTrue(file_exists($live_test_file), "Could not find test file in live version of site");
    }

    /**
     * Return an array containing information about the site
     *
     * @return array
     */
    private function getSiteInfo()
    {
        $site_info['name'] = SmoketestHelper::getDefaultInstall();

        // get server_meta data
        $server_meta_data = SmoketestHelper::getServerMetaInfo();

        // grab appropriate ip info
        if (array_key_exists('cluster-metadata', $server_meta_data)) {
            $site_info['ip'] = $server_meta_data['cluster-metadata']['lb_ip'];
            $site_info['sftp_ip'] = $server_meta_data[$server_meta_data['cluster-metadata']['sftp_instance']]['ip_pub'];
        } else {
            $site_info['ip'] = $server_meta_data['pod']['ip'];
            $site_info['sftp_ip'] = $server_meta_data['pod']['ip'];
            $site_info['sftp_endpoint'] = $server_meta_data['pod']['sftp_endpoint'];
        }
        return $site_info;
    }

    /**
     * Return true if copy to staging has completed
     *
     * @return bool
     */
    private function getStagingStatus()
    {
        $plugin = new \WpeCommon();
        try {
            $staging_status = $plugin->get_staging_status();
        } catch (\Exception $e) {
            return false;
        }

        return $staging_status;
    }
}
