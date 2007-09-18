var currentIter = 0;
var lastIter = 0;
var maxIter = 0;
var slideShowElement = "";
var slideShowData = new Array();
var slideShowInit = 1;
var slideShowDelay = 9000;
var articleLink = "";

function initSlideShow(element, data) {
	slideShowElement = element;
	slideShowData = data;
	element.style.display="block";
	
	//articleLink = document.createElement('a');
  //articleLink.className = 'global';
	//element.appendChild(articleLink);
	//articleLink.href = "";
	
	maxIter = data.length;
	for(i=0;i<data.length;i++)
	{
		var currentImg = document.createElement('img');
		currentImg.setAttribute('id','slideElement' + parseInt(i));
		currentImg.style.position="absolute";
		currentImg.style.left=data[i][1];
		currentImg.style.bottom=data[i][2];
		currentImg.style.margin="0px";
		currentImg.style.border="0px";
		currentImg.src=data[i][0];
	
		//articleLink.appendChild(currentImg);
		element.appendChild(currentImg);
		currentImg.currentOpacity = new fx.Opacity(currentImg, {duration: 400});
		currentImg.currentOpacity.setOpacity(0);
	}
	
	currentImg.currentOpacity = new fx.Opacity(currentImg, {duration: 400});
	currentImg.currentOpacity.setOpacity(0);
	
	var slideInfoZone = document.createElement('div');
	slideInfoZone.setAttribute('id','slideInfoZone');
	slideInfoZone.combo = new fx.Combo(slideInfoZone);
	slideInfoZone.combo.o.setOpacity(0);
	//articleLink.appendChild(slideInfoZone);
	element.appendChild(slideInfoZone);
	doSlideShow();
}

function startSlideShow() {
	lastIter = currentIter+1;
	if (currentIter >= maxIter)
	{
		currentIter = 0;
		lastIter = maxIter - 1;
	}
	slideShowInit = 0;
	doSlideShow();
}


function nextSlideShow() {
	lastIter = currentIter;
	currentIter++;
	if (currentIter >= maxIter)
	{
		currentIter = 0;
		lastIter = maxIter - 1;
	}
	doSlideShow();
	slideShowInit = 0;
}

function doSlideShow() {
	//alert(currentIter);
	if (slideShowInit == 1)
	{
		setTimeout(startSlideShow,10);
		//setTimeout(nextSlideShow,10);
	} else { 
		if (currentIter != 0) {
			$('slideElement' + parseInt(currentIter)).currentOpacity.options.onComplete = function() {
				$('slideElement' + parseInt(lastIter)).currentOpacity.setOpacity(0);
			}
			$('slideElement' + parseInt(currentIter)).currentOpacity.custom(0, 1);
		} else {
			$('slideElement' + parseInt(currentIter)).currentOpacity.setOpacity(1);
			$('slideElement' + parseInt(lastIter)).currentOpacity.custom(1, 0);
		}
		setTimeout(showInfoSlideShow,1000);
		setTimeout(hideInfoSlideShow,slideShowDelay-1000);
		setTimeout(nextSlideShow,slideShowDelay);
	}	
}

function showInfoSlideShow() {
	slideShowElement.removeChild($('slideInfoZone'));
	var slideInfoZone = document.createElement('div');
	slideInfoZone.setAttribute('id','slideInfoZone');
	slideInfoZone.combo = new fx.Combo(slideInfoZone);
	slideInfoZone.combo.o.setOpacity(0);
	var slideInfoZoneTitle = document.createElement('h2');
	slideInfoZoneTitle.innerHTML = slideShowData[currentIter][3]
	slideInfoZone.appendChild(slideInfoZoneTitle);
	var slideInfoZoneDescription = document.createElement('p');
	slideInfoZoneDescription.innerHTML = slideShowData[currentIter][4]
	slideInfoZone.appendChild(slideInfoZoneDescription);
	slideShowElement.appendChild(slideInfoZone);
	
	//articleLink.href = slideShowData[currentIter][1];
	
	slideInfoZone.combo.o.custom(0, 0.7);
	slideInfoZone.combo.h.custom(0, slideInfoZone.combo.h.el.offsetHeight);
}

function hideInfoSlideShow() {
	$('slideInfoZone').combo.o.custom(0.7, 0);
	//$('slideInfoZone').combo.h.custom(slideInfoZone.combo.h.el.offsetHeight, 0);
}