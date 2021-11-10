/**
 * BlockModule
 *
 * @package     BlockModule
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
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