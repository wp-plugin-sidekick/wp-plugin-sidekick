/**
 * Addon Builder App.
 */

import React, {useState, useRef} from 'react';
import { __ } from '@wordpress/i18n';

import {
	useState,
	useEffect,
	useContext,
	useReducer,
	createContext
} from 'react';

export const AomContext = createContext([{}, function() {}]);

export function createManageableAddons( initial ) {
	const manageableAddOns = {};
	
	// Create a state handler for each addon.
	for ( const addOn in initial ) {
		manageableAddOns[initial[addOn].dirname] = useAddon(initial[addOn]);
	}
	
	return manageableAddOns;
}

function useAddon( addOn ) {

	// Create a state handler for each module.
	for ( const module in addOn.modules ) {
		addOn.modules[module] = useModule(addOn.modules[module]);
	}
	
	const [data, set] = useState(addOn);
	const ref = useRef(data);
	
	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setDataAsync(newState) {
		ref.current = newState;
		set(newState);
	}

	function setDevStatus( statusName, statusValue ) {
		const newAddonData = JSON.parse( JSON.stringify( ref.current ) );
		if ( ! newAddonData.devStatus ) {
			newAddonData.devStatus = {}
		}

		newAddonData.devStatus[statusName] = statusValue;
		setDataAsync(newAddonData);
	}

	return {
		data: ref.current,
		set: setDataAsync,
		setDevStatus,
	}
}

function useModule( module ) {
	const [data, set] = useState(module);
	
	function setModuleStatus( statusName, statusValue ) {
		const newModuleData = JSON.parse( JSON.stringify( data ) );
		if ( ! newModuleData.status ) {
			newModuleData.status = {}
		}
		newModuleData.status.javascript = statusValue;
		set(newModuleData);
	}

	return {
		data,
		set,
		setModuleStatus,
	}
}

export function useCurrentAddOn() {
	const [currentAddOn, setCurrentAddOn] = useState();
	const [currentModule, setCurrentModule] = useState();
	
	// When the plugin/add-on is changed, reset the current module to be null.
	useEffect(() => {
		setCurrentModule( null );
	}, [currentAddOn] );

	

	return {
		currentAddOn,
		setCurrentAddOn,
		currentModule,
		setCurrentModule,
	}
}

export function runShellCommand( props ) {
	
	return new Promise((resolve, reject) => {
		
		fetch('https://pluginbuilder.local/wp-json/wpps/v1/runshellcommand', {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				location: props.location,
				job_identifier: props.currentAddOn.data.dirname + '_' + props.job_identifier,
				command: props.command,
			})
		})
		.then( response => response.json())
		.then( ( data ) => {
			//console.log(data);
			props.currentAddOn.setDevStatus( props.job_identifier, data );
			resolve( data );
		});

	});
}

export async function killModuleShellCommand( props ) {
	const rawResponse = await fetch('https://pluginbuilder.local/wp-json/wpps/v1/killmoduleshellcommand', {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			job_identifier: props.currentAddOn.data.dirname + '_' + props.job_identifier,
		})
	});
	const content = await rawResponse.json();

	return content;
}


export function phpcsDo( props ) {
	
	return new Promise((resolve, reject) => {
		
		fetch('https://pluginbuilder.local/wp-json/wpps/v1/phpcs?' + new URLSearchParams({
				location: props.location,
				job_identifier: props.currentAddOn.data.dirname + '_' + props.job_identifier,
			}), {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
		})
		.then( response => response.json())
		.then( ( data ) => {
			props.currentAddOn.setDevStatus( props.job_identifier, data );
			resolve( data );
		});

	});
	
}

export async function enableDevelopmentMode(currentAddOn) {
	// Enable phpcs.
	phpcsDo({
		location: currentAddOn.data.dirname,
		job_identifier: 'phpcs',
		currentAddOn: currentAddOn,
	}).then( () => {
		runShellCommand({
			location: currentAddOn.data.dirname,
			job_identifier: 'npm_run_dev_js',
			command: 'npm run dev:js',
			currentAddOn: currentAddOn,
		});
	}).then( () => {
		runShellCommand({
			location: currentAddOn.data.dirname,
			job_identifier: 'npm_run_dev_css',
			command: 'npm run dev:css',
			currentAddOn: currentAddOn,
		});
	});
}

export async function disableDevelopmentMode(currentAddOn) {
	// Kill phpcs.
	killModuleShellCommand({
		location: currentAddOn.data.dirname,
		job_identifier: 'phpcs',
		currentAddOn: currentAddOn,
	});
	
	// Kill npm_run_dev:js.
	killModuleShellCommand({
		location: currentAddOn.data.dirname,
		job_identifier: 'npm_run_dev_js',
		currentAddOn: currentAddOn,
	});
	
	// Kill npm_run_dev:css.
	killModuleShellCommand({
		location: currentAddOn.data.dirname,
		job_identifier: 'npm_run_dev_css',
		currentAddOn: currentAddOn,
	});
	
}