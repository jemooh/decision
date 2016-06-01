var apiUrl = "api";
var negotiationsPanel, optionsPanel, infoPanel;
var logged = false;
var STATE_INIT = 1;
var pointTimeout;
var timeout;
function message( text )
{
    $( '#loading' ).html( text );
}

function do_login( login, password )
{
    if ( !logged )
    {
        $( '#login_form' ).hide();
        $.ajax(
        {
            cache: false,
            url: apiUrl + "/login",
            dataType: "json",
            method: "POST",
            data:
            {
                'login': login,
                'password': password
            },
            success: function ( result )
            {
                $( '#negotiations' ).empty();
                $( '#options' ).empty();
                $( '#opponent' ).empty();
                $( '#balance' ).empty();
                message( "" );
                $( '#decision_panel' ).show();
                $( '#signout' ).show();
                if ( result.status == 'ok' )
                {
                    logged = true;
                    createCookie( "login", login, 1 );
                    createCookie( "password", password);
                    if ( result.negotiations.length == 0 )
                    {
                        negotiationsPanel.html( 'You are not involved in any negotiations' );
                    }
                    else
                    {
                        $.each( result.negotiations, function ( index, negotiation )
                        {
                            var negotiationButton = $( "<div class=\"btn btn-link\"></div>" );
                            negotiationButton.html( "<a href=\"#\">" + ( negotiation.title ) + "</a>" );
                            negotiation.active = negotiation.active == "1";
                            if ( negotiation.active )
                            {
                                negotiationButton.addClass( 'active' );
                            }
                            negotiationButton.click( function ()
                            {
                                initializeUser( negotiation.negotiations_users_id, negotiation.id, negotiation.opponent, negotiation.active );
                            } );
                            negotiationsPanel.append( negotiationButton );
                        } );
                    }
                    //initializeUser(status.id);
                }
                else
                {
                    $( '#login_form' ).show();
                    $( '#signout' ).hide();
                    alert( "Fail" );
                }
            }
        } );
    }
}
$( document ).ready( function ()
{
    $( '#signout' ).hide();
    var saved_login = readCookie( 'login' );
    var saved_password = readCookie( 'password' );
    if ( saved_login != null && saved_password != null )
    {
        $( '#login_form' ).hide();
        do_login( saved_login, saved_password );
    }
    negotiationsPanel = $( '#decision_panel #negotiations' );
    optionsPanel = $( '#decision_panel #options' );
    ratingsPanel = $( '#decision_panel #ratings' );
    infoPanel = $( '#decision_panel #info' );
    $( '#login_form form' ).submit( function ()
    {
        var login = $( '#login' ).val();
        var password = $( '#password' ).val();
        do_login( login, $.md5( password ) );
        return false;
    } );
    $( "#signout form" ).submit( function ()
    {
        eraseCookie( "login" );
        eraseCookie( "password" );
        $( '#login_form' ).show();
        $( '#signout' ).hide();
        $( '#decision_panel' ).hide();
        clearTimeouts();
        logged = false;
    } );
} );

function clearTimeouts(){

 if ( timeout != undefined )
    {
        clearTimeout( timeout );
    }

    if ( pointTimeout != undefined )
    {
        clearTimeout( pointTimeout );
    }

}
function initializeUser( userID, negotiationID, opponentID, active )
{

    clearTimeouts();  



    $.when( loadNegotiation( negotiationID, userID ), loadOptions( negotiationID ), loadPoints( negotiationID, userID, opponentID ) ).then( function ( negotiationResult, optionsResult, pointsResult )
    {
        var negotiation = new Negotiation( negotiationID, userID, opponentID, optionsPanel, ratingsPanel, infoPanel, active, optionsResult[ 0 ] );
        negotiation.clear();
        if ( pointsResult[ 0 ].length != 0 )
        {
            negotiation.processPoints( pointsResult[ 0 ] );
        }
        else
        {
            negotiation.createRatingTable( optionsResult[ 0 ] )
        }
        if ( !active )
        {
            negotiation.disable();
        }
        negotiation.processSettings( negotiationResult[ 0 ][ 0 ] );
    } );
}

function loadOptions( negotiationID )
{
    var login = readCookie( 'login' );
    var password = readCookie( 'password' );
    return $.ajax(
    {
        cache: false,
        beforeSend: function ( xhr )
        {
            xhr.setRequestHeader( "Authorization", "Basic " + btoa( login + ":" + password ) );
        },
        url: apiUrl + "/negotiations/" + negotiationID + "/options",
        dataType: "json",
        /*data:  yourJsonData*/
    } );
}

function loadNegotiation( negotiationID, userID )
{
    return $.ajax(
    {
        cache: false,
        url: apiUrl + "/negotiations/" + negotiationID + "/users/" + userID + "/settings",
        dataType: "json",
        /*data:  yourJsonData*/
    } );
}

function loadPoints( negotiationID, userID, opponentID )
{
    return $.ajax(
    {
        cache: false,
        url: apiUrl + "/negotiations/" + negotiationID + "/users/" + userID + "/opponents/" + opponentID + "/points",
        dataType: "json",
        /*data:  yourJsonData*/
    } );
}

function loadMyLastOffer( negotiationID, userID )
{
    return $.ajax(
    {
        cache: false,
        url: apiUrl + "/negotiations/" + negotiationID + "/users/" + userID + "/offers/last",
        dataType: "json",
        /*data:  yourJsonData*/
    } );
}

function Negotiation( negotiationID, userID, opponentID, optionsPanel, ratingsPanel, infoPanel, active, options )
{
    this.buttons = [];
    this.options = options;
    this.negotiationID = negotiationID;
    this.userID = userID;
    this.opponentID = opponentID;
    this.lastOfferID = 0;
    this.state = STATE_INIT;
    this.lastSentOption = 0;
    this.lastOptionID = 0;
    this.optionsPanel = optionsPanel;
    this.ratingsPanel = ratingsPanel;
    this.infoPanel = infoPanel;
    var that = this;
    this.active = active;
    if ( !active )
    {
        this.optionsPanel.prop( 'disabled', true );
    }
    this.setSate
    this.clear = function ()
    {
        $( this.optionsPanel ).html( "" );
        $( this.ratingsPanel ).html( "" );
        $( this.infoPanel ).find( '#opponent' ).html( "" );
        $( this.infoPanel ).find( '#balance' ).html( "" );
        message( "" );
    }
    this.disable = function ()
    {
        $.each( that.buttons, function ( i, button )
        {
            button.disable();
        } );
    }
    this.isOpponentReady = function ( pointsResult )
    {
        return pointsResult != null && pointsResult.length > 0 && pointsResult[ 0 ].points !== null;
    }
    this.addOption = function ( option )
    {
        var button = new Button( option, this.optionsPanel, this.negotiationID, this.userID, this );
        this.buttons.push( button );
    }
    this.processMyLastOffer = function ( myLastOfferResult )
    {
        if ( myLastOfferResult.length > 0 && myLastOfferResult[ 0 ].option_id !== 'undefined' )
        {
            this.lastSentOption = myLastOfferResult[ 0 ].option_id;
            this.findButton( myLastOfferResult[ 0 ].option_id ).sentOffer();
        }
    }
    this.processSettings = function ( result )
    {
        var balance_tag = this.infoPanel.find( '#balance' );
        balance_tag.html( result.balance == null ? "" : "Balance: " + Math.round( result.balance * 100 ) / 100 );
        var opponent_tag = this.infoPanel.find( '#opponent' );
        opponent_tag.html( "You vs. " + result.name );
    }
    this.createRatingTable = function ( options )
    {
        var tag = this.ratingsPanel;
        var options_tag = this.optionsPanel;
        options_tag.hide();
        tag.show();
        tag.append($("<div class=\"column-left\">Dislike</div>"));
        tag.append($("<div class=\"column-right\">Like</div>"));
        var div = $("<div class=\"column-center\"></div>");
        var form = $( "<form></form>" );
        $.each( options, function ( i, option )
        {
            var row = $( '<div></div>' );
            var col1 = $( '<div></div>' );
            var col2 = $( '<div></div>' );
            col1.append( option.title );
            var input = $( "<input data-slider-id=\"ex1Slider\" data-slider-min=\"0\" data-slider-max=\"1\" data-slider-step=\"0.01\" data-slider-value=\"0.5\">" );
            input.attr( 'name', 'preferences[' + option.id + "]" );
            col2.append( input );
            input.slider(
            {} );
            row.append( col1 );
            row.append( col2 );
            form.append( row );
        } );
        form.append( "<input type=\"submit\"/>" );

        form.on( 'submit', function ()
        {
            $.ajax(
            {
                cache: false,
                url: apiUrl + "/negotiations/" + that.negotiationID + "/users/" + userID + "/preferences/",
                method: "POST",
                dataType: "json",
                data: form.serialize(),
                success: function ( result )
                {
                    options_tag.show();
                    $.when( loadPoints( that.negotiationID, that.userID, that.opponentID ) ).then( function ( result )
                    {
                        that.processPoints( result, that.options );
                    } );
                    tag.hide();
                }
            } );
            return false;
        } );
        div.append( form );
        tag.append( div );
        
    }
    this.findButton = function ( id )
    {
        var result = null;
        $.each( this.buttons, function ( i, button )
        {
            if ( button.optionID == id )
            {
                result = button;
            }
        } );
        return result;
    }
    this.processPoints = function ( pointsResult )
    {
        var that = this;
        if ( this.isOpponentReady( pointsResult ) )
        {
            this.clear();
            var points = {}
                //this.optionsPanel.html("");
            $.each( pointsResult, function ( i, point )
            {
                points[ point.option_id ] = point;
            } );
            $.each( that.options, function ( i, option )
            {
                that.options[ i ].point = points[ option.id ];
            } );
            that.options.sort( function ( a, b )
            {
                return b.point.myrating - a.point.myrating;
            } );
            $.each( that.options, function ( i, option )
            {
                that.addOption( option );
            } );
            $.when( loadMyLastOffer( this.negotiationID, this.userID ) ).then( function ( result )
            {
                that.processMyLastOffer( result );
            } );
            that.loadLastOffer();
            that.optionsPanel.show();
            that.ratingsPanel.hide();
        }
        else
        {
            message( "Waiting for opponent's input" );
            pointTimeout = setTimeout( function ()
            {
                $.when( loadPoints( that.negotiationID, that.userID, that.opponentID ) ).then( function ( result )
                {
                    that.processPoints( result, that.options );
                } );
            }, 5000 );
        }
    }
    this.loadLastOffer = function ()
    {
        var _this = this;
        
        $.ajax(
        {
            cache: false,
            url: apiUrl + "/negotiations/" + this.negotiationID + "/users/" + this.userID + "/offers/incoming/since/" + this.lastOfferID + "/last",
            dataType: "json",
            success: function ( result )
            {
                if ( result.length == 1 )
                {
                    if ( result[ 0 ].id != undefined )
                    {
                        if ( _this.lastOfferID != 0 )
                        {
                            _this.findButton( _this.lastOptionID ).clearRecievedOffer();
                        }
                        _this.findButton( result[ 0 ].option_id ).recievedOffer();
                        _this.lastOptionID = result[ 0 ].option_id;
                        _this.lastOfferID = result[ 0 ].id;
                    }
                    if ( result[ 0 ].agreement == "true" )
                    {
                        message( "Agreement reached" );
                        if ( result[ 0 ].agreement_action != undefined )
                        {
                            eval( result[ 0 ].agreement_action );
                        }
                        _this.active = false;
                        _this.disable();
                    }
                }
            }
        } );
        //
        if ( _this.active )
        {
            timeout = setTimeout( function ()
            {
                _this.loadLastOffer();
            }, 2000 );
        }
    }
}

function Button( option, parentPanel, negotiationID, userID, negotiations )
{
    this.optionID = option.id;
    this.negotiations = negotiations;
    this.parentPanel = parentPanel;
    this.tag = $( "<button class=\"btn btn-default col-centered\"></button>" );
    this.disable = function ()
    {
        this.tag.prop( 'disabled', true );
    }
    if ( option.point == undefined )
    {
        this.tag.prop( 'disabled', true );
    }
    var rating = option.point != undefined ? " (value: " + Math.round( option.point.myrating * 10 ) / 10 + ", bonus: " + option.point.points + ")" : "(no rating)";
    this.tag.html( option.title + rating );
    var button = this;
    this.tag.click( function ()
    {
        sendOffer( negotiationID, userID, option.id, button );
    } );
    $( this.parentPanel ).append( this.tag );
    this.recievedOffer = function ()
    {
        this.tag.addClass( "btn-warning" );
    }
    this.clearRecievedOffer = function ()
    {
        this.tag.removeClass( "btn-warning" );
    }
    this.sentOffer = function ()
    {
        this.tag.addClass( "btn-success" );
    }
    this.clearSentOffer = function ()
    {
        this.tag.removeClass( "btn-success" );
    }
}

function sendOffer( nID, uID, oID, button )
{
    $.ajax(
    {
        cache: false,
        url: apiUrl + "/negotiations/" + nID + "/users/" + uID + "/offers",
        dataType: "json",
        method: "POST",
        data:
        {
            'option_id': oID
        },
        success: function ( result )
        {
            if ( button.negotiations.lastSentOption != 0 )
            {
                button.negotiations.findButton( button.negotiations.lastSentOption ).clearSentOffer();
            }
            button.sentOffer();
            button.negotiations.lastSentOption = button.optionID;
        },
        error: function ( xhr, textStatus, errorThrown )
        {
            //message("<b>" + (JSON.stringify(xhr.responseText).replace(/\\n/gm, "<br />").replace(/\t/gm, "&nbsp;")) + "</b>");
        }
    } );
}