/**
 * Tailwind Block
 *
 * @package
 */

 import { registerBlockType } from '@wordpress/blocks';
 import { __ } from '@wordpress/i18n';
 
 import './../../css/src/index.scss';
 import '../../../../css/src/tailwind.css';
 
 registerBlockType('blockmodule/blockmodule', {
	 title: __('Tailwind Block Module'),
	 category: 'common',
	 apiVersion: 2,
 
	 edit() {
		 return 'Hi there';
	 },
 
	 save() {},
 });
 