/** 立即執行(只執行1次)的Func寫法 */
(function ($) {		'use strict';


	/* 測 reday 後,做事情 */
	$(function(){
console.info('redayredayreday');	// @@

		var oDrag 	= document.getElementById("DragWin");
		setupForOthers(oDrag);
		ProcBtnTools(oDrag);	// 監聽按鈕列動作


		/* 點擊 */
		$("#btnTest").on('click', function(e) {
			console.warn('btnTest_click: '+e.target);	// @@
		});



	});




/**	○●○●○●○●○●○●○●○●○●○●○●○●○●○●○●
 *	○●○●○●○●○●○●○●○●○●○●○●○●○●○●○● */
	/* 針對 touch裝置 安裝 */
	function setupForOthers(el) {
		var $el = $(el);
		setDraggable($el);		// 設定 元素可拖動
		setResizable($el);		// 設定 可調整大小

		/* 設定 可調整大小 */
		function setResizable($tar) {
			// const MinWidth = 120, MinHeight = 100	// 預設最小寬高

			$tar.resizable({
				helper: "ui-resizable-helper",	// 助手(拉就出現框)
				/* 限制 最大/最小尺寸 */
				maxHeight:	Math.floor(window.innerHeight * 0.85),	// 畫面的 85%
				maxWidth:	Math.floor(window.innerWidth * 0.95),	// 畫面的 95%
				// minHeight:	MinHeight,
				// minWidth:	MinWidth,
				minHeight:	100,
				minWidth:	120,
				// aspectRatio: 16 / 9,	// 保持比例
				/* 監聽 resizestop (reSize結束) */
				// stop: function(event, ui) {
					// // console.debug('Debug_height: '+ui.size.height);	// @@
					// /* 更新 寬高值 及通知 改變大小 */
					// UpdateWH(ui.size.width-2, ui.size.height-2); // 扣掉border
				// }
			});
		}


		/* 設定 元素可拖動 */
		function setDraggable($tar) {
			const isTouch = 0;	// @@

			const touchs = {
				tap:	isTouch ? 'tap' 		: 'click',
				down:	isTouch ? 'touchstart' 	: 'mousedown',
				move:	isTouch ? 'touchmove' 	: 'mousemove',
				up: 	isTouch ? 'touchend' 	: 'mouseup', // $.touchs.up
				leave:	isTouch ? 'touchCancel' : 'mouseleave'
			};


			var dragged		/** @Remember: event來源(currentTarget)一定是整個DragWin */
				,$h2  	= $tar.find(".title h2")
				,_down	= touchs.down
				,_up	= touchs.up
				,_txt 	= $h2.html();

			$tar.off(_down).on(_down, onMouseDown);
			$tar.off(_up).on(_up, onMouseUp);
			$tar.draggable({
				start: onStartDrag,		// 開始
				drag: onMovingDrag,		// 移動中
				stop: onStopDrag		// 停止
			});

			function onMouseDown() {
				dragged = false;
			}
			function onMouseUp(e) {
				/** @PS: 還原-內容滑動的標題 */			
				if( !dragged ) { $h2.html(_txt); }
			}
			/* 針對拖移 */
			function onStartDrag(e) {
				const elm 	  = e.target
					,_attr	  = "draggable"
					,hasFind  = elm.id == "DragWin"
						|| $(elm).children()[0].hasAttribute(_attr)
					,_tagName = e.target.tagName
					// ,bDrag 	  = (hasFind && _tagName != 'TOOLS') ? 1 : 0;
					,bDrag 	  = hasFind;	// @@

				var _memo = 't目標: '+(elm.id || _tagName)+', 拖動: '+bDrag;
				console.debug(_memo);	// @@

				/** @Fix: 修正jquery會將wrap內容元素變可拖 */
				if(bDrag) {
					dragged = true;
				} else {
					$h2.html('內容滑動中..');
					/** @Important: ※return false取代preventDefault */
					return false;	// PS: 用以不讓jquery拖移而已
					// e.stopPropagation();	// 針對 parent handlers
					// e.preventDefault();
					// e.stopImmediatePropagation(); // NG: 針對所有在此區域內的handlers
				}
			}

			function onMovingDrag() { 
				$h2.html('視窗移動中..');
			}
			/* 停止拖移 */
			function onStopDrag() {
				$h2.html(_txt);
			}
		}

	}

	/* 按鈕列作用區 */
	function ProcBtnTools(oDrag) {
		/* 獲取id, class, tagName */
		var get = {
			// byId : function (id) {
				// return typeof id === "string" ? document.getElementById(id) : id
			// },
			byClass : function (sClass, oParent) {
				var aClass	 = []
					,reClass = new RegExp("(^| )" + sClass + "( |$)")
					,aElem	 = this.byTagName("*", oParent);
				for (var i = 0; i < aElem.length; i++)
					reClass.test(aElem[i].className) && aClass.push(aElem[i]);
				return aClass
			},
			byTagName : function (elem, obj) {
				return (obj || document).getElementsByTagName(elem)
			}
		};

		var oMin = get.byClass("min", oDrag)[0]
		,oMax	 = get.byClass("max", oDrag)[0]
		,oRevert = get.byClass("revert", oDrag)[0]
		,oClose	 = get.byClass("close", oDrag)[0]
		,nLstWidth, nLstheight		// 記錄 當前寬高值
		,curX, curY;					// 記錄目前位置

		// 最大化 按鈕
		oMax.onclick = function () {
			/* 記錄目前按鈕之位置 */
			curX = oDrag.offsetLeft,
			curY = oDrag.offsetTop,
			/* 記錄放大前,元素之寬高 */
			nLstWidth	= oDrag.clientWidth,
			nLstheight	= oDrag.clientHeight,

			oDrag.style.top = oDrag.style.left = 0,
			// oDrag.style.marginTop = oDrag.style.marginLeft = 0,
			oDrag.style.width = document.documentElement.clientWidth - 2 + "px",
			oDrag.style.height = document.documentElement.clientHeight - 2 + "px";
			this.style.display = "none";
			oRevert.style.display = "block";

			/* 更新 寬高值 及通知 改變大小 */
			UpdateWH(oDrag.clientWidth, oDrag.clientHeight);
		};
		// 還原(最大化 按鈕	// ps: 共有2顆還原鈕
		oRevert.onclick = function () {
			oDrag.style.width	= nLstWidth + "px",
			oDrag.style.height	= nLstheight + "px";
			oDrag.style.left 	= curX + "px",
			oDrag.style.top	 	= curY + "px";

			this.style.display = "none";
			oMax.style.display = "block";

			/* 更新 寬高值 及通知 改變大小 */
			UpdateWH(nLstWidth, nLstheight);
		};
		// 最小化 按鈕
		oMin.onclick = function () {
			/* 記錄目前按鈕之位置 */	/** @PS: 測過用 jquery抓較準確~ */
			var curPOS = $(this).offset();
			curX = curPOS.left, curY = curPOS.top;	// 存目前此顆鈕位置
			oDrag.style.display = "none";
			/* 拿掉動畫(animated xx) */
			oDrag.removeAttribute("class");	// 移除整個類別
			AppVent.trigger('OnDrag_Min', true);	// 通知 最小化了

			var oA = document.createElement("a");
			// oA.className = "open";
			oA.id = "DragRest";
			oA.href = "";
			oA.title = "還原視窗";
			document.body.appendChild(oA);

			/* 更改還原鈕之位置 */
			oA.style.position = "absolute";
			oA.style.left = curX + "px",
			oA.style.top  = curY + "px";

			// 還原(最小化 按鈕	// ps: 共有2顆還原鈕
			oA.onclick = function () {
				// console.warn('oA_click_還原');	// @@
				oDrag.style.display = "block";
				document.body.removeChild(this);
				this.onclick = null;	// 相當.one的用法
				AppVent.trigger('OnDrag_Min', false);	// 通知 還原小化了
			};
		};
		//關閉 按鈕		// Hank: View那邊會處理~!
		// oClose.onclick = function () {
			// console.error('click 關閉按鈕');		// @@
		// };

		//阻止冒泡
		oMin.onmousedown = oMax.onmousedown = oClose.onmousedown = function (e) {
			// console.error('阻止冒泡22');		// @@
			this.onfocus = function () { this.blur(); };
			(e || window.event).cancelBubble = true;
			(e || window.event).preventDefault();
		};
	}





})(jQuery);




		// $("#checkCode1").on('click', createCode);
		// $("#checkCode1").on('click', function(e) {

		// });


/* On/Off Css */
// function onOffCss(id,on){
	// on = on || false;
	// try {
		// document.getElementById(id).disabled = !on;
	// } catch (e)	{
		// console.error('OffCss(),No ID found: '+id);
	// }
// }


