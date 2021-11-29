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

// Listen for tab changes in this browser.
document.addEventListener('visibilitychange', () => {
	if (document['hidden']) {
		handleVisibilityChange(false);
	} else {
		handleVisibilityChange(true);
	}
}, false );

// Also listen for alt+tab or changes to other windows (not just tabs).
document.addEventListener('focus', function() {
	handleVisibilityChange(true);
  }, false);
  
  document.addEventListener('blur', function() {
	handleVisibilityChange(false);
  }, false);
  
  window.addEventListener('focus', function() {
	  handleVisibilityChange(true);
  }, false);
  
  window.addEventListener('blur', function() {
	handleVisibilityChange(false);
  }, false);

// Hook your functions to these globals for things to run upon tab active and not active.
window.onTabActiveFunctions = [];
window.onTabUnactiveFunctions = [];

// The function which calls the hooked-in functions.
function handleVisibilityChange( tabIsVisible ) {
	if ( ! tabIsVisible ) {
		for( const tabUnactiveFunction in onTabUnactiveFunctions ) {
			onTabUnactiveFunctions[tabUnactiveFunction]();
		}
	} else {
		for( const tabActiveFunction in onTabActiveFunctions ) {
			onTabActiveFunctions[tabActiveFunction]();
		}
	}
}

export function useFetchOnRepeat( url ) {
	const [paused, setPaused] = useState( false );
	const [stopped, setStopped] = useState( true );
	const [doItAgain, setDoItAgain] = useState( false );

	const [fullResponse, setFullResponse] = useState('');

	// When the user navigates away from this tab, pause fetching on repeat.
	onTabUnactiveFunctions.push( pause );

	// When the user navigates back to this tab, resume fetching on repeat.
	onTabActiveFunctions.push( unPause );

	useEffect(() => {
		setDoItAgain( false );
		triggerAnotherRound();
	}, [doItAgain]);

	useEffect(() => {
		triggerAnotherRound();
	}, [paused, stopped] );

	function triggerAnotherRound() {
		if ( ! paused && ! stopped ) {
			setTimeout( () => {
				fetchUrl( url );
			}, 1000 );
		}
	}

	function start() {
		setStopped( false );
	}

	function stop() {
		setStopped( true );
	}

	function pause() {
		setPaused( true );
	}
	function unPause() {
		setPaused( false );
	}

	function fetchUrl( fetchUrl ) {
		// While command above is running, open a stream for its output, grabbing it from the wpps_output file in wp-content/.wppps_studio_data.
		fetch(
			fetchUrl,
			{
				method: 'GET',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				},
			}
		)
		.then(response => response.text() )
		.then( ( data ) => {
			setFullResponse(data);

			if ( ! paused && ! stopped ) {
				setDoItAgain( true );
			}

		});
	}

	return {
		start,
		stop,
		response: fullResponse,
	}
}