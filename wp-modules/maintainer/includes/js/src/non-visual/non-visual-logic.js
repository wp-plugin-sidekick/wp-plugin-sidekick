/**
 * Addon Builder App.
 */

import { __ } from '@wordpress/i18n';

import { useState, useEffect, useRef, createContext } from 'react';

export const AomContext = createContext([{}, function () {}]);

export function useStream(initial) {
	const [data, set] = useState(initial);
	const ref = useRef(data);

	useEffect(() => {
		console.log(data, ref.current);
	}, [data]);

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setDataAsync(newState) {
		ref.current = newState;
		set(newState);
		console.log(newState);
	}

	return {
		data: ref.current,
		set: setDataAsync,
	};
}

export function usePlugins(initial) {
	const [data, set] = useState(initial);
	const ref = useRef(data);

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setDataAsync(newState) {
		ref.current = newState;
		set(newState);
	}

	function setPluginDevStatus(pluginDirName, statusName, statusValue) {
		const newData = prepStateForMutation(ref.current);
		if (!newData[pluginDirName].devStatus) {
			newData[pluginDirName].devStatus = {};
		}

		newData[pluginDirName].devStatus[statusName] = statusValue;
		setDataAsync(newData);
	}

	function addNewPlugin(newPluginData) {
		const newData = prepStateForMutation(ref.current);
		newData[newPluginData.dirName] = newPluginData;
		setDataAsync(newData);
	}

	function setPluginModules(pluginDirName, newModules) {
		const newData = prepStateForMutation(ref.current);

		console.log(pluginDirName);
		newData[pluginDirName].modules = newModules;
		setDataAsync(newData);
	}

	function deleteModule(pluginDirName, moduleSlug) {
		const newData = prepStateForMutation(ref.current);
		if (newData.modules[moduleSlug]) {
			delete newData[pluginDirName].modules[moduleSlug];
		}

		setDataAsync(newData);
	}

	function setModuleDevStatus(
		pluginDirName,
		moduleSlug,
		statusName,
		statusValue
	) {
		const newData = prepStateForMutation(ref.current);
		if (!newData[pluginDirName].modules[moduleSlug].devStatus) {
			newData[pluginDirName].modules[moduleSlug].devStatus = {};
		}

		newData[pluginDirName].modules[moduleSlug].devStatus[statusName] =
			statusValue;
		setDataAsync(newData);
	}

	function setModuleName(pluginDirName, moduleSlug, newName) {
		const newData = prepStateForMutation(ref.current);
		newData[pluginDirName].modules[moduleSlug].name = newName;
		setDataAsync(newData);
	}

	return {
		data: ref.current,
		set: setDataAsync,
		addNewPlugin,
		setPluginDevStatus,
		deleteModule,
		setModuleDevStatus,
		setModuleName,
		setPluginModules,
	};
}

function useAddon(addOn) {
	// Create a state handler for each module.
	function setInitialData() {
		for (const module in addOn.modules) {
			addOn.modules[module] = useModule(addOn.modules[module]);
		}

		return addOn;
	}

	const [data, set] = useState(setInitialData());
	const ref = useRef(data);

	useEffect(() => {
		ref.current = data;
	}, [data]);

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setDataAsync(newState) {
		ref.current = newState;
		set(newState);
	}

	function setDevStatus(statusName, statusValue) {
		const newAddonData = prepStateForMutation(ref.current);
		if (!newAddonData.devStatus) {
			newAddonData.devStatus = {};
		}

		newAddonData.devStatus[statusName] = statusValue;
		setDataAsync(newAddonData);
	}

	function deleteModule(moduleSlug) {
		const newData = prepStateForMutation(ref.current);
		if (newData.modules[moduleSlug]) {
			delete newData.modules[moduleSlug];
		}

		setDataAsync(newData);
	}

	return {
		data: ref.current,
		set: setDataAsync,
		setDevStatus,
		deleteModule,
	};
}

function useModule(module) {
	const [data, set] = useState(module);
	const ref = useRef(data);

	useEffect(() => {
		ref.current = data;
	}, [data]);

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setDataAsync(newState) {
		ref.current = newState;
		set(newState);
	}

	function setModuleDevStatus(statusName, statusValue) {
		const newAddonData = prepStateForMutation(ref.current);
		if (!newAddonData.devStatus) {
			newAddonData.devStatus = {};
		}

		newAddonData.devStatus[statusName] = statusValue;
		setDataAsync(newAddonData);
	}

	function setModuleName(newName) {
		const newData = prepStateForMutation(ref.current);
		newData.name = newName;
		setDataAsync(newData);
	}

	console.log('Module Current State', ref.current);
	return {
		data: ref.current,
		set: setDataAsync,
		setModuleDevStatus,
		setModuleName,
	};
}

export function useCurrentPluginPointer() {
	const [data, set] = useState();

	return {
		data,
		set,
	};
}

export function pingGoogle(props) {
	return new Promise((resolve, reject) => {
		fetch(wppsApiEndpoints.runShellCommand, {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				location: props.location,
				job_identifier:
					props.currentPluginData.dirname +
					'_' +
					props.job_identifier,
				command: props.command,
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				resolve(data);
			});
	});
}

export function runNpmRunDev(props) {
	return new Promise((resolve, reject) => {
		fetch(wppsApiEndpoints.runShellCommand, {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				location: props.location,
				job_identifier:
					props.currentPluginData.dirname +
					'_' +
					props.job_identifier,
				command: props.command,
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				props.plugins.setPluginDevStatus(
					props.currentPluginData.dirname,
					props.job_identifier,
					JSON.parse(data)
				);
				resolve(data);
			});
	});
}

export async function killModuleShellCommand(props) {
	console.log(props);
	const rawResponse = await fetch(wppsApiEndpoints.killShellCommand, {
		method: 'POST',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
		body: JSON.stringify({
			job_identifier:
				props.currentPluginData.dirname + '_' + props.job_identifier,
		}),
	});
	const content = await rawResponse.json();

	return content;
}

export function runLinter(props) {
	return new Promise((resolve, reject) => {
		fetch(
			wppsApiEndpoints[props.job_identifier] +
				'?' +
				new URLSearchParams({
					location: props.location,
					job_identifier:
						props.currentPluginData.dirname +
						'_' +
						props.job_identifier,
				}),
			{
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
			}
		)
			.then((response) => response.json())
			.then((data) => {
				const response = JSON.parse(data);

				console.log('Data', response.output);

				// Set the entire response as a devStatus for the plugin.
				props.plugins.setPluginDevStatus(
					props.currentPluginData.dirname,
					props.job_identifier,
					response
				);

				resolve(data);
			});
	});
}

export function phplint(props) {
	return new Promise((resolve, reject) => {
		fetch(
			wppsApiEndpoints.phplint +
				'?' +
				new URLSearchParams({
					location: props.location,
					job_identifier:
						props.currentPluginData.dirname +
						'_' +
						props.job_identifier,
				}),
			{
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
			}
		)
			.then((response) => response.json())
			.then((data) => {
				const response = JSON.parse(data);

				// Set the entire response as a devStatus for the plugin.
				props.plugins.setPluginDevStatus(
					props.currentPluginData.dirname,
					props.job_identifier,
					response
				);

				let phplintJson = false;

				try {
					phplintJson = JSON.parse(response.output);
				} catch (e) {
					phplintJson = false;
				}

				if (!phplintJson) {
					resolve(data);
				}

				// But also, separate each message into the corresponding module too.
				for (module in props.currentPluginData.modules) {
					const modulePhpcsDevStatus = {};
					for (const fileName in phplintJson.files) {
						// If this phplint file is in this module, add it to this module's devStatus object.
						if (
							fileName.includes(
								props.currentPluginData.modules[module].slug
							)
						) {
							modulePhpcsDevStatus[fileName] =
								phplintJson.files[fileName];
						}
					}

					// Add the phplint data for this module to this module.
					props.plugins.setModuleDevStatus(
						props.currentPluginData.dirname,
						props.currentPluginData.modules[module].slug,
						'phplint',
						modulePhpcsDevStatus
					);
				}

				resolve(data);
			});
	});
}

export function runFixer(props) {
	return new Promise((resolve, reject) => {
		fetch(wppsApiEndpoints[props.job_identifier], {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				location: props.location,
				job_identifier:
					props.currentPluginData.dirname +
					'_' +
					props.job_identifier,
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				const response = JSON.parse(data);

				console.log('Command', response.details.command);
				// Set the entire response as a devStatus for the plugin.
				props.plugins.setPluginDevStatus(
					props.currentPluginData.dirname,
					props.job_identifier,
					response
				);

				resolve(data);
			});
	});
}

export function phpUnit(props) {
	return new Promise((resolve, reject) => {
		fetch(
			wppsApiEndpoints.phpUnit +
				'?' +
				new URLSearchParams({
					location: props.location,
					job_identifier:
						props.currentPluginData.dirname +
						'_' +
						props.job_identifier,
				}),
			{
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
			}
		)
			.then((response) => response.json())
			.then((data) => {
				const response = JSON.parse(data);

				// Set the entire response as a devStatus for the plugin.
				props.plugins.setPluginDevStatus(
					props.currentPluginData.dirname,
					props.job_identifier,
					response
				);

				resolve(data);
			});
	});
}

export async function enableDevelopmentMode(plugins, currentPluginData) {
	// Enable phplint.
	/*
	phplint({
		location: currentPluginData.dirname,
		job_identifier: 'phplint',
		currentPluginData: currentPluginData,
		plugins: plugins
	});

	runNpmRunDev({
		location: currentPluginData.dirname,
		job_identifier: 'npm_run_dev',
		command: 'npm run dev',
		currentPluginData: currentPluginData,
		plugins: plugins
	});
	*/

	pingGoogle({
		location: currentPluginData.dirname,
		job_identifier: 'pinggoogle',
		command: 'ping google.com',
		currentPluginData,
		plugins,
	});
}

export async function disableDevelopmentMode(currentPluginData) {
	// Kill phplint.
	killModuleShellCommand({
		location: currentPluginData.dirname,
		job_identifier: 'pinggoogle',
		currentPluginData,
	});

	// Kill phplint.
	killModuleShellCommand({
		location: currentPluginData.dirname,
		job_identifier: 'phplint',
		currentPluginData,
	});

	// Kill npm_run_dev.
	killModuleShellCommand({
		location: currentPluginData.dirname,
		job_identifier: 'npm_run_dev',
		currentPluginData,
	});

	// Kill npm_run_dev:css.
	killModuleShellCommand({
		location: currentPluginData.dirname,
		job_identifier: 'npm_run_dev_css',
		currentPluginData,
	});
}

function prepStateForMutation(stateToMutate, readyForMutation = false) {
	if (!readyForMutation) {
		readyForMutation = JSON.parse(JSON.stringify(stateToMutate));
	}

	for (state in stateToMutate) {
		// Check objects (that are not hooks which contain a "set" function) recursively.
		if (
			!stateToMutate[state].set &&
			typeof stateToMutate[state] === 'object'
		) {
			readyForMutation[state] = prepStateForMutation(
				stateToMutate[state],
				readyForMutation[state]
			);
		} else {
			// If this value is actually a react hook (because it contains a set function, don't destroy it and re-create it. Just use it as is.
			if (
				stateToMutate[state].set &&
				typeof stateToMutate[state].set === 'function'
			) {
				// Maintain this react hook.
				readyForMutation[state] = stateToMutate[state];
			}
		}
	}

	return readyForMutation;
}
