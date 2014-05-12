function getPorudzbine(){
	$.post("includes/getPorudzbine.php",function(response){
		try
		{
			var obj = $.parseJSON(response);
			$("#indexTabela").append("<thead><tr><th>Ime</th><th>Prezime</th><th>Napomena</th><th>Za datum</th><th>Proizvod</th><th>Cena</th><th>Slika</th></tr></thead><tbody>");
			var pom = 0;
			$.each(obj, function(i, item){
			var linija;
			//if petlja koja povlaci liniju koja odvaja datume
			if (item.datum > pom)
			{
				linija = "<tr><td class='homeLinija'></td><td class='homeLinija'></td><td class='homeLinija'></td><td class='homeLinija'></td><td class='homeLinija'></td><td class='homeLinija'></td><td class='homeLinija'></td></tr>";
				pom = item.datum;
			}
			else
			{
				linija = "";
			}
			if (item.nazivSlike == undefined ) {
				img="<td></td>";
			}
			else
				img = "<td class='indexSlikaTd'><a onclick=fancyBox('"+item.idProizvoda+"')><img class='indexSlika' src='"+"uploads/"+item.putanja+"/"+item.nazivSlike+"'/></a></td>";
			$("#indexTabela").append(linija+"<tr><td class='indexIme'>"+item.ime+"</td><td class='indexPrezime'>"+item.prezime+"</td><td class='indexNapomena'>"+item.napomena+"</td><td class='indexZaDatum'>"+item.datumTransakcije+"</td><td class='indexProizvod'>"+item.nazivProizvoda+"</td><td class='indexCena'>"+item.cena+"</td>"+img+"</tr>");
			});
			$("#indexTabela").append("</tbody>");
		}
		catch(e)
		{
			$("#indexTabela").html("<tr><th>Error occurred:</th></tr><tr><td>"+e.message+response+"</td></tr>");
		}
	});
}
function fancyBox(id){
var string = new Array();
	$.post("includes/getProizvodSlike.php", {idProizvoda:id}, function(json){
		var obj = $.parseJSON(json);
		
		$.each(obj, function(i, data){
			string.push("uploads/"+data.putanja+"/"+data.naziv);
		});
		if (string != "")
		{
		$.fancybox.open(string);
		}
		else
		{
			alert("Nema slika za izabrani proizvod.");
		}
	});
	
	//
	
}