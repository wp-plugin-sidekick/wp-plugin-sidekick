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

let anotherRoundTimeout = [];

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
	const [paused, setPaused] = useState( true );
	const pausedRef = useRef(paused);

	const [stopped, setStopped] = useState( true );
	const stoppedRef = useRef(stopped);

	const [doItAgain, setDoItAgain] = useState( false );
	const [error, setError] = useState( false );
	const [lastFetchTime, setLastFetchTime] = useState( false );
	
	const [fullResponse, setFullResponse] = useState('...');

	// When the user navigates away from this tab, pause fetching on repeat.
	onTabUnactiveFunctions.push( pause );

	// When the user navigates back to this tab, resume fetching on repeat.
	onTabActiveFunctions.push( unPause );

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setStoppedAsync(newState) {
		stoppedRef.current = newState;
		setStopped(newState);
	}

	// Keeps the state and ref equal. See https://css-tricks.com/dealing-with-stale-props-and-states-in-reacts-functional-components/
	function setPausedAsync(newState) {
		pausedRef.current = newState;
		setPaused(newState);
	}

	useEffect(() => {
		setDoItAgain( false );
		triggerAnotherRound();
	}, [doItAgain]);

	useEffect(() => {
		triggerAnotherRound();
	}, [paused, stopped] );

	function triggerAnotherRound() {
		if ( ! pausedRef.current && ! stoppedRef.current ) {
			clearTimeout( anotherRoundTimeout[url] );
			anotherRoundTimeout[url] = setTimeout( () => {
				if ( ! pausedRef.current && ! stoppedRef.current ) {
					fetchUrl( url );
				}
			}, 2000 );
		}
	}

	function start() {
		setStoppedAsync( false );
		setPausedAsync( false );
	}

	function stop() {
		setStoppedAsync( true );
	}

	function pause() {
		setPausedAsync( true );
	}
	function unPause() {
		setPausedAsync( false );
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
		.then( (response) => {
			if ( response.status !== 200 ) {
				setError( response.status );
			}
			return response.text();
		})
		.then( ( data ) => {
			setFullResponse(data);

			setLastFetchTime( Date.now() );

			if ( ! pausedRef.current && ! stoppedRef.current ) {
				setDoItAgain( true );
			}

		})
		.catch( ( error ) => {
			setLastFetchTime( Date.now() );
			console.log( 'Error', error );
			setError( error );
		});
	}

	return {
		start,
		stop,
		isStreaming: stopped ? false : true, 
		response: fullResponse,
		error: error,
		lastFetchTime: lastFetchTime
	}
}