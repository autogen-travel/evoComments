;

'use strict';

!function(e,i){if("function"==typeof define&&define.amd)define(["exports","jquery"],function(e,r){return i(e,r)});else if("undefined"!=typeof exports){var r=require("jquery");i(exports,r)}else i(e,e.jQuery||e.Zepto||e.ender||e.$)}(this,function(e,i){function r(e,r){function n(e,i,r){return e[i]=r,e}function a(e,i){for(var r,a=e.match(t.key);void 0!==(r=a.pop());)if(t.push.test(r)){var u=s(e.replace(/\[\]$/,""));i=n([],u,i)}else t.fixed.test(r)?i=n([],r,i):t.named.test(r)&&(i=n({},r,i));return i}function s(e){return void 0===h[e]&&(h[e]=0),h[e]++}function u(e){switch(i('[name="'+e.name+'"]',r).attr("type")){case"checkbox":return"on"===e.value?!0:e.value;default:return e.value}}function f(i){if(!t.validate.test(i.name))return this;var r=a(i.name,u(i));return l=e.extend(!0,l,r),this}function d(i){if(!e.isArray(i))throw new Error("formSerializer.addPairs expects an Array");for(var r=0,t=i.length;t>r;r++)this.addPair(i[r]);return this}function o(){return l}function c(){return JSON.stringify(o())}var l={},h={};this.addPair=f,this.addPairs=d,this.serialize=o,this.serializeJSON=c}var t={validate:/^[a-z_][a-z0-9_]*(?:\[(?:\d*|[a-z0-9_]+)\])*$/i,key:/[a-z0-9_]+|(?=\[\])/gi,push:/^$/,fixed:/^\d+$/,named:/^[a-z0-9_]+$/i};return r.patterns=t,r.serializeObject=function(){return new r(i,this).addPairs(this.serializeArray()).serialize()},r.serializeJSON=function(){return new r(i,this).addPairs(this.serializeArray()).serializeJSON()},"undefined"!=typeof i.fn&&(i.fn.serializeObject=r.serializeObject,i.fn.serializeJSON=r.serializeJSON),e.FormSerializer=r,r});

const app = {};

(function($) {
  	app.Document = $(document);

  	app.Actions = {
  		init: function(){
  			$(document).on('click', '[data-evocomments-action]', function(e){
  				e.preventDefault();
  				let action = $(this).data('evocomments-action');
  				app.Actions[action](e);
  			});
  		},
  		saveSettings: function(){
  			let data = $('#settingsForm').serializeObject();
  			app.Ajax('saveSettings', data, function(res){
  				if(res.status && res.status=='ok') {
  					app.Message('Сохранено');
  				}
  			});	
  		},
      restoreComment: function(e){
        let c_id = $(e.target).closest('[data-evocomments-id]').data('evocomments-id');
        app.Ajax('changeStatus', {status:0, id:c_id}, function(res){
          if(res.status && res.status=='ok') {
            $(e.target).closest('[data-evocomments-id]').removeClass('status-9');
            $(e.target).closest('[data-evocomments-id]').addClass('status-0');
          }
        }); 
      },
      destroyComment: function(e){
        if(confirm('Комментарий будет удалён безвозвратно.')) {
          let c_id = $(e.target).closest('[data-evocomments-id]').data('evocomments-id');
          app.Ajax('destroyComment', {id:c_id}, function(res){
            if(res.status && res.status=='ok') {
              $(e.target).closest('[data-evocomments-id]').remove();
            }
          }); 
        }
      },
      deleteComment: function(e){
        let c_id = $(e.target).closest('[data-evocomments-id]').data('evocomments-id');
        app.Ajax('changeStatus', {status:9, id:c_id}, function(res){
          if(res.status && res.status=='ok') {
            $(e.target).closest('[data-evocomments-id]').removeClass('status-0');
            $(e.target).closest('[data-evocomments-id]').addClass('status-9');
          }
        }); 
      },
      publishComment: function(e){
        let c_id = $(e.target).closest('[data-evocomments-id]').data('evocomments-id');
        app.Ajax('changeStatus', {status:0, id:c_id}, function(res){
          if(res.status && res.status=='ok') {
            $(e.target).closest('[data-evocomments-id]').remove();
          }
        }); 
      }
  	};


  	app.Message = function(msg) {
  		//console.log(window.parent);
  		$('#commentsModule').append('<div class="alert-message">'+msg+'</div>');
  		$('.alert-message').fadeIn();
  		setTimeout(function(){
  			$('.alert-message').fadeOut(300, function(){$('.alert-message').remove();})
  		}, 1500);
  	};

  	app.Ajax = function(action, data, callback){
  		$.ajax({
  			url: '/assets/modules/evocomments/ajax.php',
  			type: 'post',
  			dataType: 'json',
  			data: {action:action, data:data},
  			beforeSend: function(){
  				window.parent.modx.main.work();
  			}
  		})
  		.done(function(res) {
  			callback(res);
  		})
  		.fail(function() {
  			console.log("error");
  		})
  		.always(function(){
  			window.parent.modx.main.stopWork();
  		});
  	};

    app.statusSelect = {
      init: function(){
        $(document).on('change', 'select[name="statusSelect"]', function(e){
          e.preventDefault();
          let status = $(this).val();
          console.log(status);
          app.Ajax('loadComments', {status:status}, function(res){
            if(res.status && res.status=='ok') {
              $('table.grid>tbody').html(res.rows);
            }
          }); 
        });
      }
    }


 	app.Actions.init();
  app.statusSelect.init();
})(jQuery);
