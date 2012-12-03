<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/driver/foobar.php';

class phpbb_avatar_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$this->phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$this->phpbb_container->expects($this->any())
			->method('get')
			->with('avatar.driver.foobar')->will($this->returnValue('avatar.driver.foobar'));

		// Prepare dependencies for avatar manager and driver
		$config = new phpbb_config(array());
		$request = $this->getMock('phpbb_request');
		$cache = $this->getMock('phpbb_cache_driver_interface');
		$this->avatar_foobar = $this->getMock('phpbb_avatar_driver_foobar', array('get_name'), array($config, $request, $phpbb_root_path, $phpEx, $cache));
		$this->avatar_foobar->expects($this->any())
            ->method('get_name')
            ->will($this->returnValue('avatar.driver.foobar'));
		$avatar_drivers = array($this->avatar_foobar);

		// Set up avatar manager
		$this->manager = new phpbb_avatar_manager($config, $avatar_drivers, $this->phpbb_container);
	}

	public function test_get_driver()
	{
		$driver = $this->manager->get_driver('avatar.driver.foobar', true);
		$this->assertEquals('avatar.driver.foobar', $driver);

		$driver = $this->manager->get_driver('avatar.driver.foo_wrong', true);
		$this->assertNull($driver);
	}

	public function test_get_valid_drivers()
	{
		$valid_drivers = $this->manager->get_all_drivers();
		$this->assertArrayHasKey('avatar.driver.foobar', $valid_drivers);
		$this->assertEquals('avatar.driver.foobar', $valid_drivers['avatar.driver.foobar']);
	}

	public function test_get_avatar_settings()
	{
		$avatar_settings = $this->manager->get_avatar_settings($this->avatar_foobar);

		$expected_settings = array(
			'allow_avatar_' . get_class($this->avatar_foobar)	=> array('lang' => 'ALLOW_' . strtoupper(get_class($this->avatar_foobar)), 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
		);

		$this->assertEquals($expected_settings, $avatar_settings);
	}
}
