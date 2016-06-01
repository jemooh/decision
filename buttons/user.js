function User(preferences, points){

	this.preferences = {};
	

	this.orderedPreferences = [];
	this.points = {};

	this.capital = 0;

	for(var i = 0; i < preferences.length; i++){

		this.orderedPreferences.push([preferences[i]['option_id'], preferences[i]['value']]); 
		this.points[points[i]['option_id']] = points[i]['points']; 
		this.preferences[points[i]['option_id']] = preferences[i]['value']; 
	}

	this.orderedPreferences.sort(function(a, b) {return b[1] - a[1];});



	this.getMax = function () {

		return this.orderedPreferences[0][1];

	};

	this.getMin = function () {

		return this.orderedPreferences[this.orderedPreferences.length - 1][1];

	};

	this.getMaxOption = function () {

		return this.orderedPreferences[0][0];

	};

	this.getMinOption = function () {

		return this.orderedPreferences[this.orderedPreferences.length - 1][0];

	};


	this.getPreferences = function () {

		return preferences;

	};

	this.getOption = function (i) {

		return this.orderedPreferences[i][0];

	};

	this.getOptionRatingByID = function (id) {

		return this.preferences[id];

	};

	this.addCapital = function (x) {

		return this.capital += x;

	};
	
	this.getPointsByOptionID = function (option_id) {

		return this.points[option_id];

	}

	this.calculateHisBenefit = function (opponent, option_id) {

		
		var myMaxOption = this.getMaxOption();
		var hisRatingOfMyMax = opponent.getOptionRatingByID(myMaxOption);
		var hisrating = opponent.getOptionRatingByID(option_id);
		return hisrating - hisRatingOfMyMax;

	};
	

}
