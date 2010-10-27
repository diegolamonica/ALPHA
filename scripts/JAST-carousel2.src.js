/*
 *  +-------------------+
 *  | UL				|
 *  | +---------------+ |
 *  | | LI			  | |
 *  | +---------------+ |
 *  | +---------------+ |
 *  | | LI			  | |
 *  | +---------------+ |
 *  | +---------------+ |
 *  | | LI			  | |
 *  | +---------------+ |
 *  +-------------------+
 *
 *  +-----------------------------------------------+
 *  |DIV#CAROUSEL_FOR_UL						    |
 *  |+---+ +---------------------------------++---+ |
 *  ||   | |DIV								 ||   | |
 *  ||DIV| |+-------------------------------+||DIV|	|
 *  ||	 | || UL							|||   |	|
 *  ||	 | || +-------+ +-------+ +-------+ |||   |	|
 *  ||	 | || | LI    | | LI	| | LI	  | |||   |	|
 *  ||	 | || |       | |       | |       | |||   |	|
 *  ||	 | || +-------+ +-------+ +-------+ |||   |	|
 *  |+---+ |+-------------------------------+||   |	|
 *  |+---+ +---------------------------------++---+ |
 *  +-----------------------------------------------+
 * 
 */

var BP_BEFORE_AFTER= 	1;
var BP_BOTH_BEFORE=		2;
var BP_BOTH_AFTER=		3;
var BP_NOWHERE= 		0;


_.extend('Carousel',{
	
	options: {
		width: 					'308px',
		height: 				'120px',
		scrollOnX: 				true,
		scrollOnY: 				false,
		containerClassName: 	'carousel-container',
		mainContainerClassName: 'carousel-main-container',
		childNodeClassName:		'carousel-item',
		prevNavigatorClassName:	'carousel-prev',
		nextNavigatorClassName: 'carousel-next',
		navigatorPosition:		 BP_BEFORE_AFTER,
		prevNavigatorLabel:		'Previous',
		nextNavigatorLabel:		'Next',
		step:					2,
		speed:					50,
		'': ''
	},
	_options: [],
	setup: function(id, options){
		
		
		
		options = _.mergeOptions(options, this.options);
		
		var myCarousel = _._id(id);
		
		if(myCarousel){
			var D = _.DOM;
			
			// Devo creare un contenitore per il myCarousel
			
			var myCC = D.createContainer(myCarousel, 'DIV');
			
			myCC.setAttribute('id', _.generateUniqueId('carousel'));
			D.setStyle( myCC, {
				width: options.width,
				height: options.height,
				display: 'block',
				overflow: 'hidden',
				position: 'absolute'
			});
			
			D.appendClass(myCC, options.containerClassName);
			var myOverCC = D.createContainer(myCC, 'DIV');
			D.setStyle( myOverCC, {
				width: options.width,
				height: options.height,
				display: 'block',
				overflow: 'hidden'
			});
			D.appendClass(myOverCC, options.containerClassName);
			var sz = D.realSize(myCarousel);
			// Devo creare un contenitore
			D.setStyle(myCarousel, {position: 'relative', height: options.height});
			
			var myMainContainer= _.DOM.createContainer(myOverCC, 'DIV');
			D.appendClass(myMainContainer, options.mainContainerClassName);

			for(var i = 0; i < myCarousel.childNodes.length; i++){
				D.appendClass(myCarousel.childNodes[i], options.childNodeClassName);
			}
			options.startOffset = _.DOM.position(id); 
			D.setStyle(myCarousel, {
				width: options.width * _._class(options.childNodeClassName, myCarousel).length + 'px'
			});
			if(options.navigatorPosition!=BP_NOWHERE){		
				var bothBefore = options.navigatorPosition 	== BP_BOTH_BEFORE;
				var bothAfter = options.navigatorPosition 	== BP_BOTH_AFTER;
				var beforeAfter = options.navigatorPosition == BP_BEFORE_AFTER;
				
				var divLeft		= D.createOnDocument('A', (beforeAfter||bothBefore)?myOverCC:null, bothAfter?myOverCC:null, _.generateUniqueId('carousel'));
				var divRight  	= D.createOnDocument('A', bothBefore?myOverCC:null, (beforeAfter?myOverCC:bothAfter?divLeft:null), _.generateUniqueId('carousel'));
				
				D.appendClass(divLeft, options.prevNavigatorClassName);
				D.appendClass(divRight, options.nextNavigatorClassName);
				divLeft.innerHTML = options.prevNavigatorLabel;
				divRight.innerHTML = options.nextNavigatorLabel;
				divLeft.setAttribute('href', '#'+ id);
				divLeft.setAttribute('rel', id);
				divRight.setAttribute('href','#'+ id);
				divRight.setAttribute('rel', id);
				_.Events.add(divLeft, 'click', _.Carousel.movePrevious);
				_.Events.add(divRight, 'click', _.Carousel.moveNext);

			}
			
			_.Carousel._options[id] = options;
		}
		
	},
	movePrevious: 	function(event){
		var g = _.Events.generator(event);
		var id = g.getAttribute('rel');
		_.Carousel.doScroll(id, -1);
		_.Events.abort(event);
	},
	moveNext:		function(event){
		var g = _.Events.generator(event);
		var id = g.getAttribute('rel');
		_.Carousel.doScroll(id, +1);
		_.Events.abort(event);
	},
	
	doScroll: function(id, step){
		var D = _.DOM;
		var options = _.Carousel._options[id];
		var theCarousel = _._id(id);
		var pPos = D.position(theCarousel.parentNode);
		var childs = _._class(options.childNodeClassName, id);
		var p0 = D.position(childs[0]);
		
		for(var i = 0; i<childs.length; i++){
			var p = D.position(childs[i]);
		}
		for(var i = 0; i<childs.length; i++){
			var p = D.position(childs[i]);
			if(p.x>=pPos.x) break;
		}
		i +=(options.step*step);
		if(i>=childs.length) i = childs.length-1;
		if(i<0) i = 0;
		
		var newP = D.position(childs[i]);
		var scroll = newP.x-pPos.x;
		_.fx.queue([
			[
				id,
				function(id, q){
					_.fx.fadeOut(id, 2, 20, 5, q);
				}
			],
			[
				id,
				function(id, q){
					var moves =	options.step;
					if(options.scrollOnX){
						var stepX = 10 * step*-1;
						 _.fx.hscroll(id, options.speed, stepX, scroll, q);
					}
					if(options.scrollOnY){
						var stepY = sz.height/((step<0)?10:-10);
						var destinationY = sz.height*moves*step+stepY;
						_.fx.vscroll(id, options.speed, stepY, destinationY, q, true );
					}
				}
			],
			[
				id,
				function(id, q){
					
					_.fx.fadeIn(id, 2, 100, 5, q)
				}
			]
		]);
		
	}

});