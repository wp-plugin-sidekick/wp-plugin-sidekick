/**
 * Addon Builder App.
 */

import React, {useState} from 'react';
import { __ } from '@wordpress/i18n';
import { FileBrowser } from './FileBrowser/FileBrowser.js';

export function AddonBuilderApp() {
	return <div className="mx-auto">
		<div className="mx-auto grid p-5 bg-color1 text-color2 font-bold">
			{ __( 'WP Add-On Creator ayoo32', 'addonbuilder' ) }
		</div>
		<div className="container mx-auto grid grid-cols-2">
			<div className="maker">
				<PluginForm />
			</div>
			<div className="previewer">
				<FileBrowser files={[
					{
						file_one: 'File One',
					}
				]}/>
			</div>
		</div>
	</div>
}

function PluginForm( props ) {

	const [pluginName, setPluginName] = useState( 'My Awesome Plugin' );
	const [pluginDescription, setPluginDescription] = useState( 'This is my awesome plugin. It does this, and it does that too!' );
	const [pluginVersion, setPluginVersion] = useState( '1.0.0' );
	const [pluginTextDomain, setPluginTextDomain] = useState( 'my-awesome-plugin' );

	return (
		<div>
			<div className="options">
				<div className="grid gap-5 p-10">
					<h2 className="font-sans text-5xl font-black">{ __( 'Let\'s spin up a new plugin...', 'addonbuilder' ) }</h2>
					<div className="relative ">
						<label htmlFor="name-with-label" className="text-gray-700">
							{ __( 'Plugin Name', 'addonbuilder' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Name', 'addonbuilder' ) }
							value={ pluginName }
							onChange={ (event) => setPluginName( event.target.value ) }
						/>
					</div>
				
					<div className="relative ">
						<label htmlFor="name-with-label" className="text-gray-700">
							{ __( 'Plugin Description', 'addonbuilder' ) }
						</label>
						<textarea
							className="flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							id="comment"
							placeholder="Enter your plugin description"
							name="comment"
							rows="5"
							cols="40"
							value={ pluginDescription }
							onChange={ (event) => setPluginDescription( event.target.value ) }
						/>
					</div>
					
					<div className="relative ">
						<label htmlFor="name-with-label" className="text-gray-700">
							{ __( 'Plugin Version', 'addonbuilder' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Version', 'addonbuilder' ) }
							value={ pluginVersion }
							onChange={ (event) => setPluginVersion( event.target.value ) }
						/>
					</div>
					
					<div className=" relative ">
						<label htmlFor="name-with-label" className="text-gray-700">
							{ __( 'Plugin Text Domain', 'addonbuilder' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Text Domain', 'addonbuilder' ) }
							value={ pluginTextDomain }
							onChange={ (event) => setPluginTextDomain( event.target.value ) }
						/>
					</div>
					<button type="button" className="py-2 px-4  bg-green-700 hover:bg-green-400 focus:ring-green-500 focus:ring-offset-green-200 text-white w-full transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2  rounded-lg ">
						{ __( 'Next', 'addonbuilder' ) }
					</button>
				</div>
			</div>
		</div>
	)
}