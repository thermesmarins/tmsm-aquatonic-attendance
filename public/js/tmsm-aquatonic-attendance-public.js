var TmsmAquatonicAttendanceApp = TmsmAquatonicAttendanceApp || {};

(function ($, TmsmAquatonicAttendance) {
  'use strict';


  /**
   * A mixin for collections/models.
   * @see http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   * @see https://deliciousbrains.com/building-reactive-wordpress-plugins-part-1-backbone-js/
   * @see https://www.synbioz.com/blog/tech/debuter-avec-backbonejs
   */
  var AdminAjaxSyncableMixin = {
    url: TmsmAquatonicAttendanceApp.ajaxurl,
    action: 'tmsm-aquatonic-attendance-realtime',

    sync: function( method, object, options ) {

      if ( typeof options.data === 'undefined' ) {
        options.data = {};
      }

      options.data.nonce = TmsmAquatonicAttendanceApp.nonce; // From localized script.
      options.data.action_type = method;



      // If no action defined, set default.
      if ( undefined === options.data.action && undefined !== this.action ) {
        options.data.action = this.action;
      }

      console.log('sync action: '+options.data.action);
      console.log('sync options: ');
      console.log(options);


      return Backbone.sync( method, object, options );

      // Reads work just fine.
      /*if ( 'read' === method ) {
        return Backbone.sync( method, object, options );
      }

      var json = this.toJSON();
      var formattedJSON = {};

      if ( json instanceof Array ) {
        formattedJSON.models = json;
      } else {
        formattedJSON.model = json;
      }

      _.extend( options.data, formattedJSON );

      // Need to use "application/x-www-form-urlencoded" MIME type.
      options.emulateJSON = true;

      // Force a POST with "create" method if not a read, otherwise admin-ajax.php does nothing.
      return Backbone.sync.call( this, 'create', object, options );*/
    }
  };

  /**
   * A model for all your syncable models to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var BaseModel = Backbone.Model.extend( _.defaults( {
    // parse: function( response ) {
    // Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, AdminAjaxSyncableMixin ) );

  /**
   * A collection for all your syncable collections to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var BaseCollection = Backbone.Collection.extend( _.defaults( {
    // parse: function( response ) {
    // 	Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, AdminAjaxSyncableMixin ) );



  /**
   * Badge
   */
  TmsmAquatonicAttendanceApp.BadgeModel = BaseModel.extend( {
    action: 'tmsm-aquatonic-attendance-realtime',
    defaults: {
      count: null,
      capacity: null,
    }
  } );


  TmsmAquatonicAttendanceApp.BadgesCollection = BaseCollection.extend( {
    action: 'tmsm-aquatonic-attendance-realtime',
    model: TmsmAquatonicAttendanceApp.BadgeModel,

  } );

  TmsmAquatonicAttendanceApp.BadgesListView = Backbone.View.extend( {
    el: '#tmsm-aquatonic-attendance-badge-container',
    selectedValue: null,
    selectedIsVariable: null,
    selectedHasChoicesVariable: null,
    selectElement: '#tmsm-aquatonic-attendance-badge-select',
    loadingElement: '#tmsm-aquatonic-attendance-badge-loading',

    initialize: function() {
      //this.hide();
      console.log('BadgesListView initialize');
      //$( this.selectElement ).empty().val('');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },
    loading: function(){
      console.log('BadgesListView loading');
      $( this.loadingElement ).show();
      $( this.selectElement ).hide();
    },
    loaded: function(){
      console.log('BadgesListView loaded');
      $( this.loadingElement ).hide();
      $( this.selectElement ).show();
    },

    render: function() {
      var $list = this.$( this.selectElement ).empty().val('');

      //$list.hide();

      console.log('BadgesListView collection:');
      console.log(this.collection);

      //$list.append( '<option>'+TmsmAquatonicAttendanceApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        console.log(model);
        var item = new TmsmAquatonicAttendanceApp.BadgesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }
      this.loaded();

      return this;
    },

    change: function(event){
      console.log('BadgeListView change');
      this.selectedValue = $(event.target).val();
      TmsmAquatonicAttendanceApp.productAttributesList.reset();
      TmsmAquatonicAttendanceApp.productAttributesList.loading();

      console.log('selectedValue: '+this.selectedValue);

      this.selectedIsVariable = $(event.target).children("option:selected").attr('data-variable');
      console.log('selectedIsVariable: '+this.selectedIsVariable);

      var choices = JSON.parse($(event.target).children("option:selected").attr('data-choices'));
      this.selectedHasChoices = (choices.length !== 0);
      console.warn('selectedChoices: ');
      console.log(choices);
      console.log(choices.length);
      console.log('selectedHasChoices: '+this.selectedHasChoices);

      TmsmAquatonicAttendanceApp.selectedData.set('product', this.selectedValue);
      TmsmAquatonicAttendanceApp.dateList.reset();
      TmsmAquatonicAttendanceApp.timesList.reset();
      TmsmAquatonicAttendanceApp.choicesList.reset();
      TmsmAquatonicAttendanceApp.productAttributesList.reset();
      TmsmAquatonicAttendanceApp.productVariationsList.reset();

      if(this.selectedHasChoices){
        // Go to choices
        TmsmAquatonicAttendanceApp.data.choices = choices;
        TmsmAquatonicAttendanceApp.choices.reset( TmsmAquatonicAttendanceApp.data.choices );
        TmsmAquatonicAttendanceApp.choicesList.render();

        TmsmAquatonicAttendanceApp.animateTransition(TmsmAquatonicAttendanceApp.choicesList.element());
      }
      else{
        if(this.selectedIsVariable){
          // Go to variations
          TmsmAquatonicAttendanceApp.productvariations.fetch({ data: {
              product: this.selectedValue,
            } });
          TmsmAquatonicAttendanceApp.productattributes.fetch({ data: {
              product: this.selectedValue,
            } });

          TmsmAquatonicAttendanceApp.productVariationsList.matchattributes();
          //TmsmAquatonicAttendanceApp.productAttributesList.render();

          TmsmAquatonicAttendanceApp.animateTransition(TmsmAquatonicAttendanceApp.productAttributesList.element());
        }
        else{
          // Set product data
          TmsmAquatonicAttendanceApp.selectedData.set('product', this.selectedValue);
          TmsmAquatonicAttendanceApp.selectedData.set('productvariation', this.selectedValue);
          TmsmAquatonicAttendanceApp.animateTransition(TmsmAquatonicAttendanceApp.dateList.element());
        }
      }


    },

    reset: function (){
      this.$( this.selectElement ).empty().val('');
      this.selectedValue = null;
      TmsmAquatonicAttendanceApp.selectedData.set('product', null);
      this.hide();
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }
  } );


  TmsmAquatonicAttendanceApp.BadgesListItemView = Backbone.View.extend( {
    tagName: 'div',
    className: 'tmsm-aquatonic-attendance-badge',
    template: wp.template( 'tmsm-aquatonic-attendance-badge' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );

  /**
   * Retrieves new data from server.
   */
  TmsmAquatonicAttendanceApp.refreshData = function() {
    console.log('refreshData');
    TmsmAquatonicAttendanceApp.badge.fetch();
  };

  TmsmAquatonicAttendanceApp.runTimer = function() {
    if ( undefined == TmsmAquatonicAttendanceApp.timer ) {
      TmsmAquatonicAttendanceApp.timer = setInterval( TmsmAquatonicAttendanceApp.refreshData, TmsmAquatonicAttendanceApp.timer_period * 1000 );
    }
  };


  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquatonicAttendanceApp.init = function() {

    TmsmAquatonicAttendanceApp.badge = new TmsmAquatonicAttendanceApp.BadgesCollection();
    TmsmAquatonicAttendanceApp.badge.reset( TmsmAquatonicAttendanceApp.data.realtime );
    TmsmAquatonicAttendanceApp.badgeList = new TmsmAquatonicAttendanceApp.BadgesListView( { collection: TmsmAquatonicAttendanceApp.badge } );
    TmsmAquatonicAttendanceApp.badgeList.render();

    // Start a timer for updating the data.
    TmsmAquatonicAttendanceApp.runTimer();

  };

  $( document ).ready( function() {
    TmsmAquatonicAttendanceApp.init();
  } );

})(jQuery, TmsmAquatonicAttendanceApp);
