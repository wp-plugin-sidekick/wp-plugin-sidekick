/**
 * Addon Builder App.
 */

import React, {useState, useContext} from 'react';
import { __ } from '@wordpress/i18n';
import { AomContext, useCurrentAddOn, useManageableAddons, runShellCommand, killModuleShellCommand } from './../non-visual/non-visual-logic.js';
import SuitcaseIcon from './suitcase-icon.js';

export function AddonMaintainerApp() {

	const manageableAddOns = useManageableAddons( aomManageableAddOns );
	const currentAddOnController = useCurrentAddOn();
	const currentAddOn = manageableAddOns[currentAddOnController.currentAddOn];
	let currentModule;
	if ( currentAddOnController.currentAddOn && currentAddOnController.currentModule ) {
		currentModule = currentAddOn.data.modules[currentAddOnController.currentModule];
	} else {
		currentModule = null;
	}

	return <AomContext.Provider
		// Pass data into the context, which is availale in all of our components.
		value={ {
			manageableAddOns,
			currentAddOn: currentAddOn,
			setCurrentAddOn: currentAddOnController.setCurrentAddOn,
			currentModule: currentModule,
			setCurrentModule: currentAddOnController.setCurrentModule,
		} }
		>
		<div className="mx-auto p-5">
			<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
				<div className="px-2 mx-2">
					<span className="text-lg font-bold">
					WP Plugin Studio
					</span>
				</div> 
				<div className="flex flex-grow">
					<ManageableAddOns />
				</div>
				<div className="flex flex-grow-0">
					<div className="btn btn-secondary">Deploy Plugin</div>
				</div>
			</div>
			<div className="main-work-area mx-auto">
				<div>
					<AddOnData />
				</div>
				<div>
					<div className="card lg:card-side bordered bg-base-100">
						<div className="card-body">
							<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
								<div className="flex px-2 mx-2 w-full">
									<div className="flex-grow">
										<span className="text-lg font-bold">
										Custom Modules
										</span>
									</div>
								</div> 
							</div>
							<ManageableModules />
						</div>
					</div>
				</div>
			</div>
		</div>
	</AomContext.Provider>
}

function SearchField() {
	
	return(
		<div className="form-control">
			<label className="label">
				<span className="label-text">connected</span>
			</label> 
			<div className="relative">
				<input type="text" placeholder="Search" className="w-full pr-16 input input-bordered" /> 
				<button className="absolute top-0 right-0 rounded-l-none btn">go</button>
			</div>
		</div>
	)
}

function ManageableAddOns( props ) {
	const {manageableAddOns, setCurrentAddOn} = useContext(AomContext);
	
	const addOns = manageableAddOns;
	
	function rendermanageableAddOns() {
		
		const manageableAddOnsRendered = [];
		
		for ( const addOn in addOns ) {
			const currentAddOn = addOns[addOn].data.dirname;
			manageableAddOnsRendered.push(
				<option
					key={addOns[addOn].data.dirname}
					value={currentAddOn}
				>
					{addOns[addOn].data.Name}	
				</option>
				
			);
		}
		
		return manageableAddOnsRendered;
		
	}
	
	return <>
		<div className="flex mx-auto">
			<label className="label mr-4">
				<span className="label-text">Current Plugin</span> 
			</label> 
			<select
				className="select select-bordered max-w-xs text-base-content"
				tabIndex="0"
				onChange={(event) => {
					setCurrentAddOn(event.target.value)
				}}
			>
				<option disabled="">Choose a plugin to work on</option> 
				{ rendermanageableAddOns() }
			</select>
		</div>
	</>
}

function ManageableModules( props ) {
	const {currentAddOn, currentModule, setCurrentModule} = useContext(AomContext);
	
	if ( ! currentAddOn ) {
		return '';	
	}

	const modules = currentAddOn.data.modules;
	
	if ( ! modules ) {
		return 'No modules found';
	}
	
	function renderModuleActiveCss( module ) {
		if ( ! currentModule ) {
			return '';
		}
		if ( currentModule.slug === module ) {
			return ' btn-active';
		} else {
			return '';
		}
	}

	function renderModules() {
		
		const modulesRendered = [];
		
		for ( const module in modules ) {
			modulesRendered.push(
				<div
					key={modules[module].slug}
					className="alert alert-info"
					onClick={() => {
						setCurrentModule(modules[module].slug);
					}}
				>
					<div className="flex flex-1 w-full">
						<div className="flex flex-grow">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="w-6 h-6 mx-2 stroke-current">
								<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
							</svg> 
							<p>{modules[module].name}</p>
						</div>
						<div className="flex gap-4 mx-4 place-items-end flex-grow-0">
							<StatusBadge label="javascript" status="error" />
							<StatusBadge label="js-lint" status ="success" />
							<StatusBadge label="css" />
							<StatusBadge label="css-lint" />
							<StatusBadge label="translation (i18n)" />
						</div>
						<div className="flex-grow-0">
							<div className="form-control">
								<label className="cursor-pointer label">
									<span className="label-text mr-2">Enable Development Mode</span> 
									<input type="checkbox" className="toggle" onChange={ (event) => {
										if ( event.target.checked ) {
											runShellCommand(
												currentAddOn.data.dirname,
												modules[module].slug,
												'npm run dev:js;',
												'npm_run_devjs',
											);
										} else {
											killModuleShellCommand(
												currentAddOn.data.dirname,
												modules[module].slug,
												'npm_run_devjs',
											);
										}
									}
									} />
								</label>
							</div>
						</div>
					</div>
				</div>
				
			);
		}
		
		return modulesRendered;
		
	}
	
	return <div className="modules grid grid-cols-1 gap-4">
		{ renderModules() }
	</div>
}

function StatusBadge( props ) {
	
	function getStatusColor() {
		if ( ! props.status ) {
			return {};
		}
		
		if ( props.status == 'error' ) {
			return {backgroundColor: 'hsla(var(--er)/var(--tw-bg-opacity,1))'};
		}
		
		if ( props.status == 'success' ) {
			return {backgroundColor: 'hsla(var(--su)/var(--tw-bg-opacity,1))'};
		}
	}

	return (
		<div className="btn indicator">
			<div class="indicator-item badge" style={getStatusColor()}></div> 
			{ props.label }
		</div> 
	)
}

function AddOnData( props ) {
	const {currentAddOn} = useContext(AomContext);
	
	if ( ! currentAddOn ) {
		return '';	
	}

	return (
		<div>
			<div className="options">
				<div className="grid gap-5 p-10">
					<h2 className="font-sans text-5xl font-black">{ currentAddOn.data.Name }</h2>
					<div className="relative ">
						<label htmlFor="name-with-label" className="text-gray-700">
							{ __( 'Name', 'addonbuilder' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Name', 'addonbuilder' ) }
							value={ currentAddOn.data.Name }
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
							value={ currentAddOn.data.Description }
							onChange={ (event) => {
								const newData = JSON.parse( JSON.stringify( currentAddOn.data ) );
								newData.Description = event.target.value;
								currentAddOn.set( newData ) ;
							} }
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
							value={ currentAddOn.data.Version }
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
							value={ currentAddOn.data.TextDomain }
							onChange={ (event) => setPluginTextDomain( event.target.value ) }
						/>
					</div>
				</div>
			</div>
		</div>
	)
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

function SpinningGears( props ) {
	// Credit: https://codepen.io/gareys/pen/meRgLG
	return(
		<div style={{width: props.width}}>
		<svg style={{width: '100%'}} className="machine"xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 645 526">
		 <defs/>
		 <g>
		   <path  x="-173,694" y="-173,694" className="large-shadow" d="M645 194v-21l-29-4c-1-10-3-19-6-28l25-14 -8-19 -28 7c-5-8-10-16-16-24L602 68l-15-15 -23 17c-7-6-15-11-24-16l7-28 -19-8 -14 25c-9-3-18-5-28-6L482 10h-21l-4 29c-10 1-19 3-28 6l-14-25 -19 8 7 28c-8 5-16 10-24 16l-23-17L341 68l17 23c-6 7-11 15-16 24l-28-7 -8 19 25 14c-3 9-5 18-6 28l-29 4v21l29 4c1 10 3 19 6 28l-25 14 8 19 28-7c5 8 10 16 16 24l-17 23 15 15 23-17c7 6 15 11 24 16l-7 28 19 8 14-25c9 3 18 5 28 6l4 29h21l4-29c10-1 19-3 28-6l14 25 19-8 -7-28c8-5 16-10 24-16l23 17 15-15 -17-23c6-7 11-15 16-24l28 7 8-19 -25-14c3-9 5-18 6-28L645 194zM471 294c-61 0-110-49-110-110S411 74 471 74s110 49 110 110S532 294 471 294z"/>
		 </g>
		 <g>
		   <path x="-136,996" y="-136,996" className="medium-shadow" d="M402 400v-21l-28-4c-1-10-4-19-7-28l23-17 -11-18L352 323c-6-8-13-14-20-20l11-26 -18-11 -17 23c-9-4-18-6-28-7l-4-28h-21l-4 28c-10 1-19 4-28 7l-17-23 -18 11 11 26c-8 6-14 13-20 20l-26-11 -11 18 23 17c-4 9-6 18-7 28l-28 4v21l28 4c1 10 4 19 7 28l-23 17 11 18 26-11c6 8 13 14 20 20l-11 26 18 11 17-23c9 4 18 6 28 7l4 28h21l4-28c10-1 19-4 28-7l17 23 18-11 -11-26c8-6 14-13 20-20l26 11 11-18 -23-17c4-9 6-18 7-28L402 400zM265 463c-41 0-74-33-74-74 0-41 33-74 74-74 41 0 74 33 74 74C338 430 305 463 265 463z"/>
		 </g>
		 <g >
		   <path x="-100,136" y="-100,136" className="small-shadow" d="M210 246v-21l-29-4c-2-10-6-18-11-26l18-23 -15-15 -23 18c-8-5-17-9-26-11l-4-29H100l-4 29c-10 2-18 6-26 11l-23-18 -15 15 18 23c-5 8-9 17-11 26L10 225v21l29 4c2 10 6 18 11 26l-18 23 15 15 23-18c8 5 17 9 26 11l4 29h21l4-29c10-2 18-6 26-11l23 18 15-15 -18-23c5-8 9-17 11-26L210 246zM110 272c-20 0-37-17-37-37s17-37 37-37c20 0 37 17 37 37S131 272 110 272z"/>
		 </g>
		 <g>
		   <path x="-100,136" y="-100,136" className="small" d="M200 236v-21l-29-4c-2-10-6-18-11-26l18-23 -15-15 -23 18c-8-5-17-9-26-11l-4-29H90l-4 29c-10 2-18 6-26 11l-23-18 -15 15 18 23c-5 8-9 17-11 26L0 215v21l29 4c2 10 6 18 11 26l-18 23 15 15 23-18c8 5 17 9 26 11l4 29h21l4-29c10-2 18-6 26-11l23 18 15-15 -18-23c5-8 9-17 11-26L200 236zM100 262c-20 0-37-17-37-37s17-37 37-37c20 0 37 17 37 37S121 262 100 262z"/>
		 </g>
		 <g>
		   <path x="-173,694" y="-173,694" className="large" d="M635 184v-21l-29-4c-1-10-3-19-6-28l25-14 -8-19 -28 7c-5-8-10-16-16-24L592 58l-15-15 -23 17c-7-6-15-11-24-16l7-28 -19-8 -14 25c-9-3-18-5-28-6L472 0h-21l-4 29c-10 1-19 3-28 6L405 9l-19 8 7 28c-8 5-16 10-24 16l-23-17L331 58l17 23c-6 7-11 15-16 24l-28-7 -8 19 25 14c-3 9-5 18-6 28l-29 4v21l29 4c1 10 3 19 6 28l-25 14 8 19 28-7c5 8 10 16 16 24l-17 23 15 15 23-17c7 6 15 11 24 16l-7 28 19 8 14-25c9 3 18 5 28 6l4 29h21l4-29c10-1 19-3 28-6l14 25 19-8 -7-28c8-5 16-10 24-16l23 17 15-15 -17-23c6-7 11-15 16-24l28 7 8-19 -25-14c3-9 5-18 6-28L635 184zM461 284c-61 0-110-49-110-110S401 64 461 64s110 49 110 110S522 284 461 284z"/>
		 </g>
		 <g>
		   <path x="-136,996" y="-136,996" className="medium" d="M392 390v-21l-28-4c-1-10-4-19-7-28l23-17 -11-18L342 313c-6-8-13-14-20-20l11-26 -18-11 -17 23c-9-4-18-6-28-7l-4-28h-21l-4 28c-10 1-19 4-28 7l-17-23 -18 11 11 26c-8 6-14 13-20 20l-26-11 -11 18 23 17c-4 9-6 18-7 28l-28 4v21l28 4c1 10 4 19 7 28l-23 17 11 18 26-11c6 8 13 14 20 20l-11 26 18 11 17-23c9 4 18 6 28 7l4 28h21l4-28c10-1 19-4 28-7l17 23 18-11 -11-26c8-6 14-13 20-20l26 11 11-18 -23-17c4-9 6-18 7-28L392 390zM255 453c-41 0-74-33-74-74 0-41 33-74 74-74 41 0 74 33 74 74C328 420 295 453 255 453z"/>
		 </g>
	    </svg>
	    <style>
		{`
		.machine {
  width: 60vmin;
  fill: #3eb049; }

.small-shadow, .medium-shadow, .large-shadow {
  fill: rgba(0, 0, 0, 0.05); }

.small {
  -webkit-animation: counter-rotation 2.5s infinite linear;
	   -moz-animation: counter-rotation 2.5s infinite linear;
		-o-animation: counter-rotation 2.5s infinite linear;
		   animation: counter-rotation 2.5s infinite linear;
  -webkit-transform-origin: 100.136px 225.345px;
	 -ms-transform-origin: 100.136px 225.345px;
		transform-origin: 100.136px 225.345px; }

.small-shadow {
  -webkit-animation: counter-rotation 2.5s infinite linear;
	   -moz-animation: counter-rotation 2.5s infinite linear;
		-o-animation: counter-rotation 2.5s infinite linear;
		   animation: counter-rotation 2.5s infinite linear;
  -webkit-transform-origin: 110.136px 235.345px;
	 -ms-transform-origin: 110.136px 235.345px;
		transform-origin: 110.136px 235.345px; }

.medium {
  -webkit-animation: rotation 3.75s infinite linear;
	   -moz-animation: rotation 3.75s infinite linear;
		-o-animation: rotation 3.75s infinite linear;
		   animation: rotation 3.75s infinite linear;
  -webkit-transform-origin: 254.675px 379.447px;
	 -ms-transform-origin: 254.675px 379.447px;
		transform-origin: 254.675px 379.447px; }

.medium-shadow {
  -webkit-animation: rotation 3.75s infinite linear;
	   -moz-animation: rotation 3.75s infinite linear;
		-o-animation: rotation 3.75s infinite linear;
		   animation: rotation 3.75s infinite linear;
  -webkit-transform-origin: 264.675px 389.447px;
	 -ms-transform-origin: 264.675px 389.447px;
		transform-origin: 264.675px 389.447px; }

.large {
  -webkit-animation: counter-rotation 5s infinite linear;
	-moz-animation: counter-rotation 5s infinite linear;
		-o-animation: counter-rotation 5s infinite linear;
	   	animation: counter-rotation 5s infinite linear;
  -webkit-transform-origin: 461.37px 173.694px;
	 -ms-transform-origin: 461.37px 173.694px;
		transform-origin: 461.37px 173.694px; }

.large-shadow {  
  -webkit-animation: counter-rotation 5s infinite linear;
	   -moz-animation: counter-rotation 5s infinite linear;
		-o-animation: counter-rotation 5s infinite linear;
		   animation: counter-rotation 5s infinite linear;
  -webkit-transform-origin: 471.37px 183.694px;
	 -ms-transform-origin: 471.37px 183.694px;
		transform-origin: 471.37px 183.694px; }

@-webkit-keyframes rotation {
    from {-webkit-transform: rotate(0deg);}
    to   {-webkit-transform: rotate(359deg);}
}
@-moz-keyframes rotation {
    from {-moz-transform: rotate(0deg);}
    to   {-moz-transform: rotate(359deg);}
}
@-o-keyframes rotation {
    from {-o-transform: rotate(0deg);}
    to   {-o-transform: rotate(359deg);}
}
@keyframes rotation {
    from {transform: rotate(0deg);}
    to   {transform: rotate(359deg);}
}

@-webkit-keyframes counter-rotation {
    from {-webkit-transform: rotate(359deg);}
    to   {-webkit-transform: rotate(0deg);}
}
@-moz-keyframes counter-rotation {
    from {-moz-transform: rotate(359deg);}
    to   {-moz-transform: rotate(0deg);}
}
@-o-keyframes counter-rotation {
    from {-o-transform: rotate(359deg);}
    to   {-o-transform: rotate(0deg);}
}
@keyframes counter-rotation {
    from {transform: rotate(359deg);}
    to   {transform: rotate(0deg);}
}`}

	    </style>
	    </div>
	)
}