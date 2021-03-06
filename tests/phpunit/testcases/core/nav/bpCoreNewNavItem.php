<?php
/**
 * @group core
 * @group nav
 */
class BP_Tests_Core_Nav_BpCoreNewNavItem extends BP_UnitTestCase {

	public function test_user_nav() {
		$bp_nav = buddypress()->bp_nav;

		$u = $this->factory->user->create();
		$old_current_user = get_current_user_id();
		$this->set_current_user( $u );

		$this->go_to( bp_core_get_user_domain( $u ) );

		bp_core_new_nav_item( array(
			'name'                    => 'Foo',
			'slug'                    => 'foo',
			'position'                => 25,
			'screen_function'         => 'foo_screen_function',
			'default_subnav_slug'     => 'foo-sub'
		) );

		$expected = array(
			'name'                    => 'Foo',
			'slug'                    => 'foo',
			'link'                    => trailingslashit( bp_core_get_user_domain( $u ) . 'foo' ),
			'css_id'                  => 'foo',
			'show_for_displayed_user' => true,
			'position'                => 25,
			'screen_function'         => 'foo_screen_function',
			'default_subnav_slug'     => 'foo-sub'
		);

		$this->assertSame( buddypress()->bp_nav['foo'], $expected );

		// Clean up
		buddypress()->bp_nav = $bp_nav;
		$this->set_current_user( $old_current_user );
	}

	public function test_group_nav() {
		$bp_nav = buddypress()->bp_nav;

		$u = $this->factory->user->create();
		$g = $this->factory->group->create();
		$old_current_user = get_current_user_id();
		$this->set_current_user( $u );

		$group = groups_get_group( array(
			'group_id' => $g,
		) );

		$this->go_to( bp_get_group_permalink( $group ) );

		$this->assertTrue( buddypress()->bp_nav[ $group->slug ]['position'] === -1 );

		// Clean up
		buddypress()->bp_nav = $bp_nav;
		$this->set_current_user( $old_current_user );
	}

	public function test_should_return_false_if_name_is_not_provided() {
		$args = array(
			'slug' => 'foo',
		);

		$this->assertFalse( bp_core_new_nav_item( $args ) );
	}

	public function test_should_return_false_if_slug_is_not_provided() {
		$args = array(
			'name' => 'foo',
		);

		$this->assertFalse( bp_core_new_nav_item( $args ) );
	}

	public function test_should_return_false_if_site_admin_only_and_current_user_cannot_bp_moderate() {
		// Should already be set to a 0 user.
		$this->assertFalse( bp_current_user_can( 'bp_moderate' ) );
		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'site_admin_only' => true,
		);

		$this->assertFalse( bp_core_new_nav_item( $args ) );
	}

	public function test_css_id_should_fall_back_on_slug() {
		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
		);
		bp_core_new_nav_item( $args );

		$this->assertSame( 'foo', buddypress()->bp_nav['foo']['css_id'] );
	}

	public function test_css_id_should_be_respected() {
		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'item_css_id' => 'bar',
		);
		bp_core_new_nav_item( $args );

		$this->assertSame( 'bar', buddypress()->bp_nav['foo']['css_id'] );
	}

	public function test_show_for_displayed_user_false_should_force_function_to_return_false_when_bp_user_has_access_is_also_false() {
		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'show_for_displayed_user' => false,
		);

		add_filter( 'bp_user_has_access', '__return_false' );
		$retval = bp_core_new_nav_item( $args );
		remove_filter( 'bp_user_has_access', '__return_false' );

		$this->assertFalse( $retval );
	}

}
