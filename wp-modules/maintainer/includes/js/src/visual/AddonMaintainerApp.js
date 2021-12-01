/**
 * Addon Builder App.
 */

import React, {useState, useContext, useRef, useEffect} from 'react';
import { __ } from '@wordpress/i18n';
import {
	AomContext,
	useCurrentPluginPointer,
	usePlugins,
	useStream,
	runShellCommand,
	killModuleShellCommand,
	phpcsDo,
	enableDevelopmentMode,
	disableDevelopmentMode
} from './../non-visual/non-visual-logic.js';

import {
	useFetchOnRepeat,
} from './../non-visual/useFetchOnRepeat.js';


export function AddonMaintainerApp() {
	const plugins = usePlugins( wppsPlugins );
	console.log( plugins.data );
	
	const currentPluginPointer = useCurrentPluginPointer();
	const currentPluginData = currentPluginPointer.data ? plugins.data[currentPluginPointer.data] : false;
	console.log( currentPluginData );
	
	return <AomContext.Provider
		// Pass data into the context, which is availale in all of our components.
		value={ {
			plugins: plugins,
			currentPluginData: currentPluginData,
			setCurrentPlugin: currentPluginPointer.set,
		} }
		>
		<div className="mx-auto p-5 relative">
			<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box z-10 relative">
				<div className="flex-grow px-2 mx-2">
					<span className="text-lg font-bold">
					WP Plugin Studio - A place to invent things with WordPress.
					</span>
				</div> 
				<div className="flex flex-grow-0">
					<ManageableAddOns />
					<div className="flex p-5">
						or
					</div>
					<div className="flex">
						<CreatePluginButtonAndModal />
					</div>
				</div>
				
			</div>
			<PreFlightChecks />
			<Plugin />
		</div>
	</AomContext.Provider>
}

function Plugin() {
	const {currentPluginData} = useContext(AomContext);

	if ( ! currentPluginData ) {
		return '';	
	}

	return (
		<div className="card lg:card-side bordered bg-base-100 w-full">
			<div className="card-body">
				<AddOnHeader />
				<div className="grid grid-cols-2 w-full gap-4 mx-auto">
					
						
						<DevArea />
					
				
						<div >
							<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
								<div className="flex px-2 mx-2 w-full">
									<div className="flex-grow">
										<span className="text-lg font-bold">
										Custom Modules
										</span>
									</div>
									<div className="flex flex-grow-0">
										<CreateModuleButtonAndModal />
										
									</div>
								</div> 
							</div>
							<ManageableModules />
						</div>
					
				</div>
			</div>
		</div>
	)
}
function CreateModuleButtonAndModal() {

	const [modalOpen, setModalOpen] = useState(false);

	function maybeRenderModal() {
		if ( ! modalOpen ) {
			return '';
		}

		return(
			<Modal title="Create a new module" closeModal={ () => { setModalOpen( false ) } }>
				<div>
					<ModuleForm uponSuccess={ () => { setModalOpen( false ); }} />
				</div>
			</Modal>
		)
	}

	return (
		<>
			<button
				className="btn btn-secondary"
				onClick={() => {
					setModalOpen( true )
				}}
			>Create A New Module</button>
			{ maybeRenderModal() }
		</>
	)

}

function CreatePluginButtonAndModal() {

	const [modalOpen, setModalOpen] = useState(false);

	function maybeRenderModal() {
		if ( ! modalOpen ) {
			return '';
		}

		return(
			<Modal title="Create a new plugin" closeModal={ () => { setModalOpen( false ) } }>
				<div>
					<PluginForm uponSuccess={ () => { setModalOpen( false ); }} />
				</div>
			</Modal>
		)
	}

	return (
		<>
			<button
				className="btn btn-secondary"
				onClick={() => {
					setModalOpen( true )
				}}
			>Create A New Plugin</button>
			{ maybeRenderModal() }
		</>
	)

}

function AddOnHeader() {
	const {currentPluginData} = useContext(AomContext);
	
	if ( ! currentPluginData ) {
		return '';	
	}

	return (
		<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box z-0 relative">
			<div className="flex flex-grow px-2 mx-2">
				<span className="text-lg font-bold">
				{currentPluginData.Name}
				</span>
			</div> 
			<div className="flex flex-grow-0">
				<button className="btn btn-secondary" disabled={ currentPluginData ? false : true }>Deploy Plugin</button>
			</div>
		</div>
	)
}

function DevArea() {
	const {plugins, currentPluginData} = useContext(AomContext);
	const [currentTab, setCurrentTab] = useState(1);
	
	// NPM Run Dev file streamer.
	const npmRunDevFileStreamer = useFetchOnRepeat( '/wp-content/wpps-studio-data/wpps_output_' + currentPluginData.dirname + '_pinggoogle' );

	if ( ! currentPluginData ) {
		return '';	
	}

	return (
		<div className="grid grid-cols-1 gap-4 grid-flow-row auto-rows-min">
			<div>
				<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
					<div className="flex px-2 mx-2 w-full">
						<div className="flex-grow">
							<span className="text-lg font-bold">
							Development
							</span>
						</div>
						<span className="text-lg mr-4">
							Enable development mode 
						</span>
						<input type="checkbox" className="toggle" onChange={ (event) => {
							if ( event.target.checked ) {
								enableDevelopmentMode( plugins, currentPluginData );
								npmRunDevFileStreamer.start();
							} else {
								disableDevelopmentMode( currentPluginData );
								npmRunDevFileStreamer.stop();
							}
						}
						} />
					</div>
				</div>
				<div className="">
					<div className="tabs tabs-boxed z-10">
						{(() => {
							const status = currentPluginData.devStatus ? currentPluginData.devStatus['npm_run_dev'] : false;
							return (
								<>
								<div onClick={() => { setCurrentTab( 1 )}}>
									<StatusBadge key={'npm_run_dev'} label={'npm_run_dev'} status={ status } active={ 1 === currentTab } />
								</div>
								<div onClick={() => { setCurrentTab( 2 )}}>
									<StatusBadge key={'npm_run_dev'} label={'npm_run_dev'} status={ status } active={ 2 === currentTab } />
								</div>
								</>
							)
						})() }
					</div>
					<div className="z-0">
						{(() => {
							npmRunDevFileStreamer.response
							if ( currentTab !== 1 || ! npmRunDevFileStreamer.response ) {
								return '';
							}
							console.log( npmRunDevFileStreamer.response );

							return (
								<>
									<TerminalWindow>
										{ npmRunDevFileStreamer.response }
									</TerminalWindow>
									
								</>
							)
						})() }
					</div>
				</div>
			</div>
			<div>
				<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
					<div className="flex px-2 mx-2 w-full">
						<div className="flex-grow">
							<span className="text-lg font-bold">
							Testing and Linting
							</span>
						</div>
						<span className="text-lg mr-4">
							Test
						</span>
						<input type="checkbox" className="toggle" onChange={ (event) => {
							if ( event.target.checked ) {
								enableDevelopmentMode( plugins, currentPluginData );
							} else {
								disableDevelopmentMode( currentPluginData );
							}
						}
						} />
					</div>
				</div>
			</div>
		</div>
	)
}

function ManageableAddOns( props ) {
	const {plugins, setCurrentPlugin, currentPluginData} = useContext(AomContext);
	
	function renderplugins() {
		
		const pluginsRendered = [];
		
		for ( const plugin in plugins.data ) {
			pluginsRendered.push(
				<option
					key={plugins.data[plugin].dirname}
					value={plugins.data[plugin].dirname}
				>
					{plugins.data[plugin].Name}	
				</option>
			);
		}
		
		return pluginsRendered;
		
	}
	
	return <>
		<div className="flex">
			<label className="label mr-4">
				<span className="label-text">Current Plugin</span> 
			</label> 
			<select
				className="select select-bordered max-w-xs text-base-content"
				tabIndex="0"
				onChange={(event) => {
					setCurrentPlugin(event.target.value)
				}}
				value={ currentPluginData ? currentPluginData.dirname : 'Choose a plugin'}
			>
				<option disabled="">Choose a plugin to work on</option> 
				{ renderplugins() }
			</select>
		</div>
	</>
}

function ManageableModules( props ) {
	const {plugins, currentPluginData} = useContext(AomContext);
	
	if ( ! currentPluginData ) {
		return '';	
	}

	const modules = currentPluginData.modules;
	
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
						//setCurrentModule(modules[module].slug);
					}}
				>
					<div className="flex flex-1 w-full">
						<div className="flex title-area w-full mr-4">
							<div className="flex">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="w-6 h-6 mx-2 stroke-current">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
								</svg>
							</div>
							<div className="block">
								<div className="block text-lg" onChange={(event) => {
									plugins.setModuleName( currentPluginData.dirname, modules[module].slug, event.target.value );
								}}>
									{ modules[module].name }
								</div>
								<p className="block">{modules[module].description}</p>
							</div>
						</div>
						<div className="flex w-full mr-4">
							<div className="flex">
								{ (() => {
									if ( modules[module].devStatus ) {
										const rendered = [];
										if (  modules[module].devStatus ) {
											rendered.push(
												<PhpCsButtonAndModal module={ modules[module] } />
											)
										}
										
										return rendered;
									}
								})()}
							</div>
						</div>
						<div className="close-button flex flex-grow-0" onClick={() => {
							plugins.deleteModule( currentPluginData.dirname, modules[module].slug );
						}}>
							<button className="btn btn-circle btn-xs md:btn-sm lg:btn-md xl:btn-lg">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-4 h-4 stroke-current md:w-6 md:h-6">   
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>                       
								</svg>
							</button>
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

function Modal( props ) {

	return(
		<div 
			style={{
				position: 'fixed',
				top: '0',
				right: '0',
				bottom: '0',
				left: '0',
				display: 'grid',
				alignContent: 'center',
				justifyContent: 'center',
				zIndex: '99999',
				backgroundColor: '#000000c7',
			}}
		>
			<div
				className="rounded-box bg-base-100"
				style={{
					maxWidth: '90vw',
					maxHeight: '90vh',
				}}
			>
				<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
					<div className="flex-1 px-2 mx-2">
						<span className="text-lg font-bold">
							{ props.title }
						</span>
					</div> 
					<div className="flex-none">
						<button className="btn btn-square btn-ghost" onClick={() => {
							props.closeModal();
						}}>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-6 h-6 stroke-current text-error">
								<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
							</svg>
						</button>
					</div>
				</div>
				<div style={{
					maxHeight: '90%',
					overflow: 'scroll',
				}}>
					{ props.children }
				</div>
			</div>
		</div>
	)

}

function PhpCsButtonAndModal( module ) {
	module = module.module;
	const [modalOpen, setModalOpen] = useState(false);

	function maybeRenderModal() {
		if ( ! modalOpen ) {
			return '';
		}

		return(
			<Modal title="Issues with PHPCS (code sniffer)" closeModal={ () => { setModalOpen( false ) } }>
				<div>
					{ renderFiles() }
				</div>
			</Modal>
		)
	}

	function getNumberOfErrors() {
		let numberOfErrors = 0;
		for( const file in module.devStatus.phpcs ){
			numberOfErrors = numberOfErrors + parseInt( module.devStatus.phpcs[ file ].errors );
		}

		return numberOfErrors;
	}

	function renderFiles() {
		
		const renderedFiles = [];
		for( const file in module.devStatus.phpcs ){
			renderedFiles.push(
				<div className="card lg:card-side bordered bg-base-100 w-full">
					<div className="card-body">
						<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
							<div className='flex-grow'>{ file }</div>
						</div>
						<div>
							{ renderMessages( module.devStatus.phpcs[ file ].messages ) }
						</div>
					</div>
				</div>
			);
		}

		return renderedFiles;
	}

	function renderMessages( messages ) {
		const renderedFileMessages = [];
		for ( const message in messages ) {
			renderedFileMessages.push(
				<div className="flex">
					<div className="flex mr-1">Line: { messages[message].line }</div>
					<div className="flex-grow">{ messages[message].message }</div>
				</div>
			)
		}
		return renderedFileMessages;
	}

	function renderButton() {
		return <>
			<button className="btn btn-secondary" onClick={ () => { setModalOpen( ! modalOpen ) } }>
				phpcs
			</button>
		</>
	}

	return (
		<>
			<div className={'indicator'}>
				<div className="indicator-item badge" style={{backgroundColor: 'hsla(var(--er)/var(--tw-bg-opacity,1))'}}>
					{ getNumberOfErrors() }
				</div>
				{ renderButton() }
			</div> 
			{ maybeRenderModal() }
		</>
	)
}

function StatusBadge( props ) {

	function maybeRenderStatusIndicator() {
		if ( props.status ) {
			return <div className="indicator-item badge" style={{backgroundColor: 'hsla(var(--er)/var(--tw-bg-opacity,1))'}}></div>
		}
	}

	return (
		<div className={'tab indicator' + ( props.active ? ' tab-active' : '' )}>
			{ maybeRenderStatusIndicator() }
			{ props.label }
		</div> 
	)
}

function PluginForm( props ) {
	const {plugins, setCurrentPlugin} = useContext(AomContext);
	const [pluginName, setPluginName] = useState( 'My Awesome Plugin' );
	const [pluginDirName, setPluginDirName] = useState( 'my-awesome-plugin' );
	const [pluginTextDomain, setPluginTextDomain] = useState( 'my-awesome-plugin' );
	const [pluginNamespace, setPluginNamespace] = useState( 'MyAwesomePlugin' );
	const [pluginDescription, setPluginDescription] = useState( 'This is my awesome plugin. It does this, and it does that too!' );
	const [pluginVersion, setPluginVersion] = useState( '1.0.0' );
	const [pluginAuthor, setPluginAuthor] = useState( 'wporgusername' );
	const [pluginUri, setPluginUri] = useState( 'yourdomain.com' );
	const [minWpVersion, setMinWpVersion] = useState( '5.8' );
	const [minPhpVersion, setMinPhpVersion] = useState( '7.2' );
	const [pluginLicense, setPluginLicense] = useState( 'GPLv2 or later' );
	const [updateUri, setUpdateUri] = useState( '' );

	useEffect( () => {
		const dirName = pluginName.replace(/\W+/g, '-').toLowerCase();
		setPluginDirName(dirName);
	}, [pluginName] );

	function createPlugin() {
		fetch(wppsApiEndpoints.generatePlugin, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				plugin_name: pluginName,
				plugin_dirname: pluginDirName,
				plugin_textdomain: pluginTextDomain,
				plugin_namespace: pluginNamespace,
				plugin_description: pluginDescription,
				plugin_uri: pluginUri,
				min_wp_version: minWpVersion,
				min_php_version: minPhpVersion,
				plugin_license: pluginLicense,
				update_uri: updateUri,
			})
		})
		.then( response => response.json())
		.then( ( data ) => {
			if ( data.success ) {
				plugins.addNewPlugin( data.plugin_data );
				setCurrentPlugin(data.plugin_data.plugin_dirname);
				props.uponSuccess();
			} else {
				alert( JSON.stringify( data ) );
			}
		});
	}

	return (
		<div>
			<div className="options">
				<div className="grid gap-5 p-10">
					<h2 className="font-sans text-5xl font-black">{ __( 'Let\'s spin up a new plugin...', 'wp-plugin-studio' ) }</h2>
					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin Name', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Name', 'wp-plugin-studio' ) }
							value={ pluginName }
							onChange={ (event) => setPluginName( event.target.value ) }
						/>
					</div>

					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin Text Domain', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Text Domain', 'wp-plugin-studio' ) }
							value={ pluginTextDomain }
							onChange={ (event) => setPluginTextDomain( event.target.value ) }
						/>
					</div>

					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin Namespace', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Namespace', 'wp-plugin-studio' ) }
							value={ pluginNamespace }
							onChange={ (event) => setPluginNamespace( event.target.value ) }
						/>
					</div>
				
					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin Description', 'wp-plugin-studio' ) }
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
						<label htmlFor="name-with-label">
							{ __( 'Plugin Version', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Version', 'wp-plugin-studio' ) }
							value={ pluginVersion }
							onChange={ (event) => setPluginVersion( event.target.value ) }
						/>
					</div>

					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin Author', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin Author', 'wp-plugin-studio' ) }
							value={ pluginAuthor }
							onChange={ (event) => setPluginAuthor( event.target.value ) }
						/>
					</div>

					<div className="relative ">
						<label htmlFor="name-with-label">
							{ __( 'Plugin URI', 'wp-plugin-studio' ) }
						</label>
						<input
							type="text"
							id="name-with-label"
							className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
							name="email"
							placeholder={ __( 'Plugin URI', 'wp-plugin-studio' ) }
							value={ pluginUri }
							onChange={ (event) => setPluginUri( event.target.value ) }
						/>
					</div>

					<button
						type="button"
						className="py-2 px-4  bg-green-700 hover:bg-green-400 focus:ring-green-500 focus:ring-offset-green-200 text-white w-full transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2  rounded-lg"
						onClick={() => {
							createPlugin();
						}}
					>
						{ __( 'Create Plugin', 'wp-plugin-studio' ) }
					</button>
				</div>
			</div>
		</div>
	)
}


function ModuleForm( props ) {
	const {plugins, setCurrentPlugin, currentPluginData} = useContext(AomContext);

	const [step, setStep] = useState( 1 );
	const [moduleName, setModuleName] = useState( 'My Awesome Module' );
	const [moduleBoiler, setModuleBoiler] = useState( null );
	const [moduleNamespace, setModuleNamespace] = useState( currentPluginData.namespace + '\\MyAwesomeModule' );
	const [moduleDescription, setModuleDescription] = useState( 'This is my awesome module. It does this, and it does that too!' );
	const boilers = wppsModuleBoilers;

	function createModule() {
		setStep( 'loading' );
		fetch(wppsApiEndpoints.generateModule, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				module_name: moduleName,
				module_namespace: moduleNamespace,
				module_description: moduleDescription,
				module_boiler: moduleBoiler,
				module_plugin: currentPluginData.dirname,
			})
		})
		.then( response => response.json())
		.then( ( data ) => {
			if ( data.success ) {
				//setCurrentPlugin(data.plugin_data.plugin_dirname);
				plugins.setPluginModules( currentPluginData.dirname, data.modules );
				//props.uponSuccess();
				setStep( 'success' );
			} else {
				setStep( 'failure' );
				alert( JSON.stringify( data ) );
			}
		});
	}

	function renderBoilerPicker() {

		const renderedBoilers = [];
		let keyCounter = 1;
		for( const boiler in boilers ) {
			renderedBoilers.push(
				renderBoilerOption( boiler, boilers[boiler], keyCounter )
			);
			keyCounter++;
		}

		return (
			<>
				<div className="grid gap-5 p-10">
					<h2 className="text-lg">{ __( 'Pick a starting point for this module', 'wp-plugin-studio' ) }</h2>
					<div className="grid gap-5">
						{ renderedBoilers }
					</div>
				</div>
			</>
		)
	}

	function renderBoilerOption( boilerName, boilerData, keyCounter ) {
		return (
			<div key={keyCounter} className="card shadow-lg compact side bg-base-200 cursor-pointer"
				onClick={() => {
					setModuleBoiler( boilerName );
					setStep( 2 );
				}}
			>
				<div className="flex-row items-center space-x-4 card-body">
					<div>
						<div className="avatar">
							<div className="rounded-full w-14 h-14 shadow bg-primary-content">
								<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQxXQXrsWLSWHf1vdT32y-xMzTipJONoU-FCQ&usqp=CAU" />
							</div>
						</div>
					</div>
					<div>
						<h2 className="card-title">{boilerData.name}</h2>
						<p className="text-base-content text-opacity-40">{boilerData.description}</p>
					</div>
				</div>
			</div>
		)
	}

	function renderStep1() {
		if ( step !== 1 ) { 
			return '';
		}

		return (
			renderBoilerPicker()
		)
	}

	function renderStep2() {
		if ( step !== 2 ) {
			return '';
		}

		return (
			<div className="grid gap-5 p-10">
				<h2 className="text-lg">{ __( 'Nice! You chose to start with this module boiler:', 'wp-plugin-studio' ) }</h2>
				<div className="card shadow-lg compact side bg-base-200 cursor-pointer"
					onClick={() => {
						setStep( 1 );
					}}
				>
					<div className="flex-row items-center space-x-4 card-body">
						<div>
							<div className="avatar">
								<div className="rounded-full w-14 h-14 shadow bg-primary-content">
									<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQxXQXrsWLSWHf1vdT32y-xMzTipJONoU-FCQ&usqp=CAU" />
								</div>
							</div>
						</div>
						<div>
							<h2 className="card-title">{boilers[moduleBoiler].name}</h2>
							<p className="text-base-content text-opacity-40">{boilers[moduleBoiler].description}</p>
						</div>
					</div>
				</div>
				<h2 className="text-lg">{ __( 'Now, let\'s set up your module data', 'wp-plugin-studio' ) }</h2>
				<div className="relative ">
					<label htmlFor="name-with-label">
						{ __( 'Module Name', 'wp-plugin-studio' ) }
					</label>
					<input
						type="text"
						id="name-with-label"
						className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
						name="email"
						placeholder={ __( 'Module Name', 'wp-plugin-studio' ) }
						value={ moduleName }
						onChange={ (event) => setModuleName( event.target.value ) }
					/>
				</div>

				<div className="relative ">
					<label htmlFor="name-with-label">
						{ __( 'Module Namespace', 'wp-plugin-studio' ) }
					</label>
					<input
						type="text"
						id="name-with-label"
						className=" rounded-lg border-transparent flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 shadow-sm text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
						name="email"
						placeholder={ __( 'Module Namespace', 'wp-plugin-studio' ) }
						value={ moduleNamespace }
						onChange={ (event) => setModuleNamespace( event.target.value ) }
					/>
				</div>
			
				<div className="relative ">
					<label htmlFor="name-with-label">
						{ __( 'Module Description', 'wp-plugin-studio' ) }
					</label>
					<textarea
						className="flex-1 appearance-none border border-gray-300 w-full py-2 px-4 bg-white text-gray-700 placeholder-gray-400 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
						id="comment"
						placeholder="Enter your module description"
						name="comment"
						rows="5"
						cols="40"
						value={ moduleDescription }
						onChange={ (event) => setModuleDescription( event.target.value ) }
					/>
				</div>

				<button
					type="button"
					className="py-2 px-4  bg-green-700 hover:bg-green-400 focus:ring-green-500 focus:ring-offset-green-200 text-white w-full transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2  rounded-lg"
					onClick={() => {
						createModule();
					}}
				>
					{ __( 'Create Module', 'wp-plugin-studio' ) }
				</button>
			</div>
		)
	}

	function renderLoadingStep() {
		if ( step !== 'loading' ) {
			return ''
		}

		return (
			<div className="grid gap-5 p-10">
				<div className="btn btn-ghost btn-sm btn-circle loading"></div>
			</div>
		)
	}

	function renderSuccessStep() {
		if ( step !== 'success' ) {
			return ''
		}

		return (
			<div className="grid gap-5 p-10">
				<h2>Module successfully created and added to plugin!</h2>
			</div>
		)
	}

	function renderFailureStep() {
		if ( step !== 'failure' ) {
			return ''
		}

		return (
			<div className="grid gap-5 p-10">
				<h2>Something went wrong</h2>
				<button
					className="btn"
					onClick={() => {
						setStep( 2 );
					}}
				>Back</button>
			</div>
		)
	}

	return (
		<>
			{ renderStep1() }
			{ renderStep2() }
			{ renderLoadingStep() }
			{ renderSuccessStep() }
			{ renderFailureStep() }
		</>
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

function PreFlighter( props ) {
	const [checkResponse, setCheckResponse] = useState( null );
	const [installResponse, setInstallResponse] = useState( null );

	const fileStreamer = useFetchOnRepeat( '/wp-content/wpps-studio-data/wpps_output_' + props.data.installJobIdentifier );

	useEffect( () => {
		if ( ! props.doingStatusChecks ) {
			fileStreamer.stop();
		} else {
			checkTheStatus();
		}
	}, [props.doingStatusChecks] );

	function checkTheStatus() {
		
		fetch(wppsApiEndpoints.whichChecker + '?' + new URLSearchParams({
			job_identifier: props.data.checkJobIdentifier,
			command: props.data.checkCommand,
		}), {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
		})
		.then( response => response.json())
		.then( ( data ) => {
			const response = JSON.parse( data );
			setCheckResponse(response);
		});

	}

	function install() {
		fetch(wppsApiEndpoints.whichChecker + '?' + new URLSearchParams({
				job_identifier: props.data.installJobIdentifier,
				command: props.data.installCommand,
			}), {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
		})
		.then( response => response.json())
		.then( ( data ) => {
			fileStreamer.stop();
			const response = JSON.parse( data );
			setInstallResponse(response);
			checkTheStatus();
		});

		fileStreamer.start();
	}


	function renderCheckStatus() {
		if ( ! checkResponse ) {
			return 'Status not yet checked...'
		}
		if ( ! checkResponse.output || checkResponse.error ) {
			return (
				<>
				<div className="alert alert-error gap-2">
					<div className="flex-1">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="w-6 h-6 mx-2 stroke-current">    
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>                      
						</svg> 
						<label>{ props.data.name + ' not found! Would you like to install it?' }</label>
					</div>
					<button
						onClick={() => {
							install();	
						}}
						class="btn btn-primary"
					>
						{ 'Install ' + props.data.name }
					</button> 
				</div>
				</>
			)
		}
		if ( checkResponse.output ) {
			return (
				<>
				<div class="alert alert-success">
					<div class="flex-1">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 mx-2 stroke-current">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>                
						</svg> 
						<label>{ props.data.name + ' exists! 👍' }</label>
					</div>
				</div>
				</>
			)
		}
	}

	function renderInstallResponse() {
		return (
			<TerminalWindow>
				{ fileStreamer.response }
			</TerminalWindow>
		)
	}
	function renderCheckResponse() {
		if ( ! checkResponse ) {
			return '';
		}

		return (
			<div>
				<TerminalWindow>
					{ checkResponse.details.command }
				</TerminalWindow>
				<TerminalWindow>
					{ checkResponse.error }
				</TerminalWindow>
				<TerminalWindow>
					{ checkResponse.output }
				</TerminalWindow>
			</div>
		)
	}

	return (

		<div className="card shadow-lg compact side bg-base-200">
			<div className="flex-row items-center space-x-4 card-body">
				<div>
					<div className="avatar">
						<div className="rounded-full w-14 h-14 shadow bg-primary-content">
							<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQxXQXrsWLSWHf1vdT32y-xMzTipJONoU-FCQ&usqp=CAU" />
						</div>
					</div>
				</div>
				<div className="grid gap-1">
					<h2 className="card-title">{props.data.name}</h2>
					<p className="text-base-content text-opacity-40">{props.data.description}</p>
					<p>{ renderCheckStatus() }</p>
					{ renderCheckResponse() }
					{ renderInstallResponse() }
				</div>
			</div>
		</div>
	)
}
function PreFlightChecks() {
	const {currentPluginData} = useContext(AomContext);
	const [doingStatusChecks, setDoingStatusChecks] = useState( false );

	if ( currentPluginData ) {
		return '';	
	}

	return (
		<>
			<div className="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-box">
				<div className="flex px-2 mx-2 w-full">
					<div className="flex-grow">
						<span className="text-lg font-bold">
						Pre Flight Checks
						</span>
					</div>
					<span className="text-lg mr-4">
						Test
					</span>
					<input type="checkbox" className="toggle" onChange={ (event) => {
						if ( event.target.checked ) {
							setDoingStatusChecks( true );
						} else {
							setDoingStatusChecks( false );
						}
					}
					} />
				</div>
			</div>
			<div className="grid gap-4">
				<PreFlighter
					data={{
						name: 'Homebrew',
						description: 'Homebrew is a way to install and manage packages on Linux/MacOS systems.',
						checkJobIdentifier: 'check_homebrew',
						checkCommand: 'brew -v;',
						installJobIdentifier: 'install_homebrew',
						installCommand: '/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"',
					}}
					doingStatusChecks={ doingStatusChecks }
				/>
				<PreFlighter
					data={{
						name: 'NodeJS',
						description: 'NodeJS runs javascript and enables package managers like NPM.',
						checkJobIdentifier: 'check_nodejs',
						checkCommand: 'nvm -v;',
						installJobIdentifier: 'install_nodejs',
						installCommand: 'brew install nvm',
					}}
					doingStatusChecks={ doingStatusChecks }
				/>
				<PreFlighter
					data={{
						name: 'NPM',
						description: 'Node Package Manager',
						checkJobIdentifier: 'check_npm',
						checkCommand: 'npm -v;',
						installJobIdentifier: 'install_npm',
						installCommand: 'brew install npm',
					}}
					doingStatusChecks={ doingStatusChecks }
				/>
				<PreFlighter
					data={{
						name: 'PHP',
						description: 'PHP on the command line enables required functionality.',
						checkJobIdentifier: 'check_php',
						checkCommand: 'php -v;',
						installJobIdentifier: 'install_php',
						installCommand: 'brew install php',
					}}
					doingStatusChecks={ doingStatusChecks }
				/>
				<PreFlighter
					data={{
						name: 'Composer',
						description: 'A Dependency Manager for PHP',
						checkJobIdentifier: 'check_composer',
						checkCommand: 'composer -v;',
						installJobIdentifier: 'install_composer',
						installCommand: 'brew install composer',
					}}
					doingStatusChecks={ doingStatusChecks }
				/>
				
			</div>
		</>
	)
}

function TerminalWindow( props ) {
	
	return (
		<div
			className="bg-black p-4 text-white z-0 whitespace-pre grid content-end overflow-y-scroll max-h-52"
		>
				{ props.children }
		</div>
	)
}