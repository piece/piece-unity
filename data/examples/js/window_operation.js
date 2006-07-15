// window_operation.js
//

var newWindows = new Array();
var index      = 0;

function windowOpen( targetObject, width, height )
{
	var uri  = targetObject.href;
	var name = targetObject.target;

	var defaultOptions = 'menubar=yes,toolbar=yes,location=yes,status=yes,scrollbars=yes,resizable=yes';
	var comma          = ',';
	var widthOption    = 'width=' + width;
	var heightOption   = 'height=' + height;
	var sizeOption     = ( width ? widthOption : '' ) + ( width && height ? comma : '' ) + ( height ? heightOption : '' );
	var options        = defaultOptions + comma + sizeOption;

	if( newWindows.length == 0 )
	{
		newWindows[index] = window.open( uri, name, options );
		newWindows[index].name = name;
	}
	else
	{
		var windowCondition  = false;
		var nullWindowIndex  = new Array();
		var count            = 0;

		for( var i = 0; i < newWindows.length; i++ )
		{
			if( newWindows[i].closed == false )
			{
				if( newWindows[i].name == name )
				{
					windowCondition = true;
					index           = i;
					break;
				}
			}
			else
			{
				nullWindowIndex[count] = i;
				count++;
			}
		}

		if( windowCondition )
		{
			var newWindow         = newWindows[index];
			var windowInnerWidth  = 0;
			var windowInnerHeight = 0;

			if( newWindow.innerWidth && newWindow.innerHeight )
			{
				windowInnerWidth  = newWindow.innerWidth;
				windowInnerHeight = newWindow.innerHeight;
			}
			else if( newWindow.document.body )
			{
				if( newWindow.document.body.clientWidth && newWindow.document.body.clientHeight )
				{
					windowInnerWidth  = newWindow.document.body.clientWidth;
					windowInnerHeight = newWindow.document.body.clientHeight;
				}
			}

			var methodType   = typeof newWindow.resizeBy;
			var noMethod     = ( methodType == 'undefined' || methodType == 'unknown' ) ? true : false;
			if( !noMethod )
			{
				var w = width  ? ( width - windowInnerWidth )   : 0;
				var h = height ? ( height - windowInnerHeight ) : 0;
				newWindow.resizeBy( w, h );
			}
		}
		else
		{
			if( nullWindowIndex.length > 0 )
			{
				index = nullWindowIndex[0];
			}
			else
			{
				index++;
			}
			newWindows[index] = window.open( uri, name, options );
			newWindows[index].name = name;
		}
	}
	newWindows[index].focus();
}

var eventFlag = true;
function getKeyCode( targetObject, eventObject )
{
	var eventType;
	var keycode;

	if( window.event )
	{
		eventType = window.event.type;
		keycode   = window.event.keyCode;
	}
	else if( eventObject )
	{
		eventType = eventObject.type;
		keycode   = eventObject.which;
	}

	if( eventType == 'keypress' && keycode == 13)
	{
		var _f = targetObject.attributes['onclick'].nodeValue;
		var f  =  _f.replace( 'this', 'targetObject' );
		eval( f );
		eventFlag = false;
	}
}