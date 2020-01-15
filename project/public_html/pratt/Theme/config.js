
function showinstructions()
{
	document.getElementById("instructions").style.display = 'block'; //make something appear
	document.getElementById("Pred").style.display = 'none';	//make something disappear 
	document.getElementById("Prey").style.display = 'none';	//make something disappear 
	document.getElementById("Player").style.display = 'none';	//make something disappear 
	document.getElementById("otheroptions").style.display = 'none';	//make something disappear 
}

function showPred()
{
	document.getElementById("Pred").style.display = 'block'; //make something appear
	document.getElementById("instructions").style.display = 'none';	//make something disappear 
	document.getElementById("Prey").style.display = 'none';	//make something disappear 
	document.getElementById("Player").style.display = 'none';	//make something disappear  
	document.getElementById("submit").style.display = 'block';
	document.getElementById("otheroptions").style.display = 'none';
}
function showPrey()
{
	document.getElementById("Prey").style.display = 'block'; //make something appear
	document.getElementById("Pred").style.display = 'none';	//make something disappear 
	document.getElementById("instructions").style.display = 'none';	//make something disappear 
	document.getElementById("Player").style.display = 'none';	//make something disappear 
	document.getElementById("submit").style.display = 'block';
	document.getElementById("otheroptions").style.display = 'none';
}

function showPlayer()
{
	document.getElementById("Player").style.display = 'block'; //make something appear
	document.getElementById("Pred").style.display = 'none';	//make something disappear 
	document.getElementById("Prey").style.display = 'none';	//make something disappear 
	document.getElementById("instructions").style.display = 'none';	//make something disappear  
	document.getElementById("submit").style.display = 'block';	 
	document.getElementById("otheroptions").style.display = 'none';
}
function showother()
{
	document.getElementById("Player").style.display = 'none'; //make something disappear
	document.getElementById("Pred").style.display = 'none';	//make something disappear 
	document.getElementById("Prey").style.display = 'none';	//make something disappear 
	document.getElementById("instructions").style.display = 'none';	//make something disappear  
	document.getElementById("submit").style.display = 'block';	 
	document.getElementById("otheroptions").style.display = 'block';
}

function showpve()
{
	document.getElementById("choice").style.display = 'none'; 
	document.getElementById("button0").style.display = 'none'; 
	document.getElementById("button00").style.display = 'none'; 
	document.getElementById("button").style.display = 'block';	
	document.getElementById("button2").style.display = 'block';	
	document.getElementById("button3").style.display = 'block';	
	document.getElementById("button4").style.display = 'block';	
	document.getElementById("home").style.display = 'block';	
	
}

function showsim()
{
	document.getElementById("home").style.display = 'block';	
	document.getElementById("choice").style.display = 'none'; 
	document.getElementById("button00").style.display = 'none'; 
	document.getElementById("button").style.display = 'block'; 
	document.getElementById("button0").style.display = 'none';	 
	document.getElementById("button1").style.display = 'block';	 
	document.getElementById("button2").style.display = 'block';	
	document.getElementById("button4").style.display = 'block';	
}

function showHome()
{
	document.getElementById("choice").style.display = 'block'; 
	document.getElementById("button00").style.display = 'block'; 
	document.getElementById("button").style.display = 'none'; 
	document.getElementById("button0").style.display = 'block';	 
	document.getElementById("button1").style.display = 'none';	 
	document.getElementById("button2").style.display = 'none';
	document.getElementById("button3").style.display = 'none';
	document.getElementById("button4").style.display = 'none';
	document.getElementById("Player").style.display = 'none'; 
	document.getElementById("Pred").style.display = 'none';	
	document.getElementById("Prey").style.display = 'none';	 
	document.getElementById("instructions").style.display = 'none';	
	document.getElementById("submit").style.display = 'none';	
	document.getElementById("otheroptions").style.display = 'none';
}