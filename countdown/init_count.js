var toDay 	= new Date(2016, 1, 23).getTime() / 1000,  //finish date
	fromDay = new Date().getTime() / 1000, //Start date
	myCountdown1 = new Countdown({
								time: toDay - fromDay, // 86400 seconds = 1 day
								width:300, 
								height:60,  
								rangeHi:"day",
								style:"flip"	// <- no comma on last item!
							});
