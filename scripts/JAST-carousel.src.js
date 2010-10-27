/*
Script Name: 	Carousel - (http://jastegg.it/eggs/Carousel/ ) 
version: 		1.0 beta
version date:	2007-11-27
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend(
	'Carousel', {
		info: {
			title: 		'Carousel',
			version:	'1.0 beta',
			eggUrl:		'http://jastegg.it/eggs/Carousel',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
	  	options: {
			id:						'div-carousel',
			label:					'JAST Carousel',
			step:					5,
			imagesPerPage:			3,
			speed:					5,
			images:					['test.jpg'],
			labels:					['test image'],
			startFrom:				1,
			thumbnailWidth:			60,
			thumbnailHeight:		80,
			leftLabel:				'Scroll left',
			rightLabel:				'Scroll right',
			navigate:				function(url){ alert(url); }
		},
		_carousel:	[],
		setup: function(options){
		  	var l = this._carousel.length;
			this._carousel[l] = JASTEggIt.mergeOptions(options, this.options);
			this.draw(l);
		},
		draw: function(idx){
		  	var shortcut = 'javascript:var x = JASTEggIt.Carousel';
			var itm = this._carousel[idx];
			
			var e = JASTEggIt._id(itm.id);
			if(e== null) return false;
			if(e.style.position!='relative') e.style.position='relative';
			var buffer = '';
			itm.id = JASTEggIt.generateUniqueId('carousel');
			buffer += '<div class="carousel-label" style="height: auto; ">' + itm.label + '</div>';
			buffer += '<' + 'a href="' + shortcut + '.move(' + idx + ', -1);" class ="left">' + itm.leftLabel + '<' + '/a>';
			buffer += '<' + 'div class="container">';
			buffer += '<' + 'div id="' + itm.id + '">';
			if (itm.href == null) itm.href = itm.images;
			if (itm.href.length<itm.images.length) itm.href.length = itm.images.length;
			for(i=0;i<itm.images.length;i++){
				buffer += '<' + 'div tabindex="0" onclick="JASTEggIt.Carousel._carousel[' + idx + '].navigate(\'' +itm.href[i] + '\')"><'+'a href="'+itm.href[i]+'"><' + 'img alt="" src="' + itm.images[i] + '" style="width:' + itm.thumbnailWidth + 'px; height: ' + itm.thumbnailHeight + 'px;';
				if(itm.background!=null){
					buffer += 'background-image: url(' + itm.background + '); ';
					
				} 
				if(itm.thumbnailWidth!=0){
					buffer += '" width="' + itm.thumbnailWidth + '" ';
				}
				if(itm.thumbnailHeight!=0){
					buffer += 'height="' + itm.thumbnailHeight + '" ';
				}
				buffer += '/><'+'span class="label">' + itm.labels[i] + '<'+'/span><'+'/a'+'><' + '/div>';
			}
			buffer += '<' + '/div>';
			buffer += '<' + '/div>';
			buffer += '<' + 'a href="' + shortcut + '.move(' + idx + ', 1);" class ="right">' + itm.rightLabel + '<' + '/a>';
			e.innerHTML = buffer;
			var e = JASTEggIt._id(itm.id);
			var li = e.firstChild;
			if(li == null) return false;
			e.style.width = (JASTEggIt.DOM.realSize(li).width * itm.images.length) + 'px';
			
		},
		move: function(idx, step){
			var minus = step<0?true:false;
			var itm = this._carousel[idx];
			if(step<0 && itm.startFrom <= 1) return false; 
			step = step * itm.imagesPerPage;	// trasformo lo step nella direzione (- o +)
			if (minus && itm.startFrom+step<1) step = 1-itm.startFrom;
			if (!minus && itm.startFrom+step+itm.imagesPerPage>itm.images.length) step = itm.images.length - itm.startFrom - itm.imagesPerPage+1;
			if(step==0) return false;
			itm.startFrom = itm.startFrom + step;
			if(itm.startFrom<1){
				itm.startFrom = 1;
				return false;	
			}
			var e = JASTEggIt._id(itm.id);
			var li = e.firstChild;
			var ret = JASTEggIt.DOM.realSize(li);
			limit = ret.width * step;
			JASTEggIt.fx.queue([
				{
					id: itm.id,
					fn: function(id, q){
						JASTEggIt.fx.fadeOut(id, 2, 20, 5, q)
					}
				},
				{
					id: itm.id,
					fn: function(id, q){
						JASTEggIt.fx.hscroll(id, itm.speed, (itm.step * (minus ? 1 : -1)), limit, q)
					}
				},
				{
					id: itm.id,
					fn: function(id, q){
						JASTEggIt.fx.fadeIn(id, 2, 100, 5, q)
					}
				}
			]);
		}
	}
);