/**
 * Addon Builder App.
 */

import React, {useState} from 'react';
import { __ } from '@wordpress/i18n';

import {
	useState,
	useEffect,
	useContext,
	useReducer,
	createContext
} from 'react';

export const AomContext = createContext([{}, function() {}]);

export function useManageableAddons( initial ) {
	const manageableAddOns = {};

	for ( const addOn in initial ) {
		manageableAddOns[initial[addOn].dirname] = useAddon(initial[addOn]);
	}
	
	console.log( manageableAddOns );
	
	return manageableAddOns;
}


export function useAddon( initial ) {
	const [data, set] = useState(initial);
	
	return {
		data,
		set,
	}
}

export function useCurrentAddOn() {
	const [currentAddOn, setCurrentAddOn] = useState();
	const [currentModule, setCurrentModule] = useState();
	const [currentModuleDevMode, setCurrentModuleDevMode] = useState();
	
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

export async function runShellCommand( plugin_dir_name, module, command, job_identifier) {
	const rawResponse = await fetch('https://pluginbuilder.local/wp-json/wpps/v1/runshellcommand', {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			plugin_dir_name: plugin_dir_name,
			module: module,
			command: command,
			job_identifier: job_identifier,
		})
	});
	const content = await rawResponse.json();

	return content;
}

export async function killModuleShellCommand( plugin_dir_name, module, job_identifier) {
	const rawResponse = await fetch('https://pluginbuilder.local/wp-json/wpps/v1/killmoduleshellcommand', {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			plugin_dir_name: plugin_dir_name,
			module: module,
			job_identifier: job_identifier,
		})
	});
	const content = await rawResponse.json();

	return content;
}
