/**
 * BlockModule
 *
 * @package     BlockModule
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './../../css/src/index.scss';

registerBlockType( 'blockmodule/blockmodule', {
	title: __( 'Block Module' ),
	category: 'common',
	apiVersion: 2,

	edit() {
		return (
      'Hi there'
    )
	},

	save() {}

} );