// - IE5.5 and up can do the blending transition.
var browserCanBlend = (is_ie5_5up);

function stopOrStart() {
	if (onoff) {
		stop();
	} else {
		play();
	}
}


function toggleLoop() {
	if (loop) {
		loop = 0;
	} else {
		loop = 1;
	}
}

function changeElementText(id, newText) {
	element = document.getElementById(id);
	element.innerHTML = newText;
}

function stop() {
	changeElementText("stopOrStartText", js_play);
	onoff = 0;
	status = js_status_stop;
	clearTimeout(timer);
}

function play() {
	changeElementText("stopOrStartText", js_stop);
	onoff = 1;
	status = unescape(js_status_playing);
	go_to_next_photo();
}


function changeDirection() {
	if (direction == 1) {
		direction = -1;
		changeElementText("changeDirText", js_forwards);
	} else {
		direction = 1;
		changeElementText("changeDirText", js_backwards);
	}
	preload_next_photo();
}

function change_transition() {
	current_transition = document.TopForm.transitionType.selectedIndex;
}

function preload_complete() {
}

function reset_timer() {
	clearTimeout(timer);
	if (onoff) {
		timeout_value = document.TopForm.time.options[document.TopForm.time.selectedIndex].value * 1000;
		timer = setTimeout('go_to_next_photo()', timeout_value);
	}
}

function wait_for_current_photo() {

	/* Show the current photo */
	if (!show_current_photo()) {
		/*
		* The current photo isn't loaded yet.  Set a short timer just to wait
		* until the current photo is loaded.
		*/
		status = js_status_loading +" (" + current_location + " " + js_of + " " + photo_count +	").  " + js_status_wait;
		clearTimeout(timer);
		timer = setTimeout('wait_for_current_photo()', 500);
		return 0;
	} else {
		status = unescape(js_status_playing);
		preload_next_photo();
		reset_timer();
	}
}

function go_to_next_photo() {
	/* Go to the next location */
	current_location = next_location;
	
	/* Show the current photo */
	if (!show_current_photo()) {
		wait_for_current_photo();
	return 0;
}

preload_next_photo();
	reset_timer();
}

function preload_next_photo() {
	/* Calculate the new next location */
	next_location = (parseInt(current_location) + parseInt(direction));
	if (next_location > photo_count) {
		next_location = 1;
		if (!loop) {
		 	stop();
		}
	}
	if (next_location == 0) {
	    next_location = photo_count;
	if (!loop) {
	 stop();
	}
}

/* Preload the next photo */
preload_photo(next_location);
}

function show_current_photo() {

	/*
	 * If the current photo is not completely loaded don't display it.
	 */
	if (!images[current_location] || !images[current_location].complete) {
		preload_photo(current_location);
		return 0;
	}
	/* transistion effects */
	if (browserCanBlend){
		var do_transition;
		if (current_transition == (transition_count)) {
		 	do_transition = Math.floor(Math.random() * transition_count);
		} else {
		 	do_transition = current_transition;
		}
		document.images.slide.style.filter=transitions[do_transition];
		document.images.slide.filters[0].Apply();
	}
	document.slide.src = images[current_location].src;
	setCaption(photo_captions[current_location]);
	
	if (browserCanBlend) {
	document.images.slide.filters[0].Play();
	}
	
	return 1;
}

function preload_photo(index) {

	/* Load the next picture */
	if (pics_loaded < photo_count) {
	
		/* not all the pics are loaded.  Is the next one loaded? */
		if (!images[index]) {
			 	images[index] = new Image;
			 	images[index].onLoad = preload_complete();
		 		images[index].src = document.getElementById("photo_urls_" + index).href;
			 	pics_loaded++;
		}
	}
}

function setCaption(text) {
	changeElementText("caption", "[" + current_location + " "+ js_of +" " + photo_count + "] " + text);
}
