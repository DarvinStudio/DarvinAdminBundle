$('.section_table').each(function(){
	var self = $(this);
	var screenHeight = window.innerHeight;
	var tableHeight = $(this).height();
	var head = $(this).find('thead tr').eq(0).clone();
	
	
	
	if(tableHeight > screenHeight*1.8){
		var counterHeight = 0;
		var countHead = tableHeight/( screenHeight*0.9);
		var interval = tableHeight / countHead;
		var intervalCurr = 0;
		var rows = self.find('tbody tr');
		
		for(var i = 0; i < rows.length; i++){
			intervalCurr += rows.eq(i).height();
			
			if(intervalCurr >= interval){
				$(head).clone().insertAfter(rows.eq(i));
				intervalCurr = 0;
			}
		}
		
	} 
	
	if(tableHeight > screenHeight*1.2){
		$(this).find('table tbody').append( head.clone() );
	}
	
});
