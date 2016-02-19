function Unloader(){
    var $this = this;
    this.unload = function(e) {
        var message = "Закрыть?";
        if (typeof e === undefined) {
            e = window.event;
        }
        if (e) {
            e.returnValue = message;
        }
        return message;
    }
 
    this.resetUnload = function() {
        $(window).off('beforeunload', $this.unload);
 
         setTimeout(function(){
            $(window).on('beforeunload', $this.unload);
        }, 1000);
    }
 
    this.init = function() {
    	//close window & press button update
        $(window).on('beforeunload', $this.unload);
        //following a link
        $('a').on('click', $this.resetUnload);
        //submit, send form
        $(document).on('submit', 'form', $this.resetUnload);
      	// F5 и Ctrl+F5, Enter
     	$(document).on('keydown', function(event){
       		if((event.ctrlKey && event.keyCode == 116) || event.keyCode == 116 || event.keyCode == 13){
       			$this.resetUnload();
       		}
        });    
    }
    this.init();
}
 
$(function(){
    if(typeof window.obUnloader != 'object')
    {
        window.obUnloader = new Unloader();
    }
})