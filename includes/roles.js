"use strict";
(function($, roles_settings) {

  let field_settings = window.contactsDetailsWpApiSettings.contacts_custom_fields_settings
  let data = null
  if (_.get(window.contactsDetailsWpApiSettings, "contact.location_grid")){
    data = {location_ids: window.contactsDetailsWpApiSettings.contact.location_grid.map(l=>l.id)}
  }
  let dispatch_users_promise = null

  let dispatch_users = [];
  //change tab
  let selected_dispatch_tab = null;
  $('#filter-tabs a').on('click', function () {
    selected_dispatch_tab = $(this).data('field')
    $('#search-users-input').attr("placeholder", $(this).text().trim())
    if ( dispatch_users_promise === null ){
      $('#dispatch-tile-loader').addClass('active')
      dispatch_users_promise = window.makeRequest( 'GET', 'dispatch-lists', data, 'dt-roles/v1/' )
      dispatch_users_promise.then(response=>{
        $('#dispatch-tile-loader').removeClass('active')
        dispatch_users = response
        $('.users-select-panel').show()
        display_dispatch_tab( selected_dispatch_tab )
      })
    } else {
      $('.users-select-panel').show()
      display_dispatch_tab( selected_dispatch_tab )
    }
  })
  let list_filters = $('#user-list-filters')
  let defined_list_section = $('#defined-lists')
  let search_section = $('#other-assign-to-typeahead')
  function display_dispatch_tab( tab = 'follow-up' ){
    let filters = `<a data-id="all" style="color: black; font-weight: bold">${_.escape(roles_settings.translations.all)}</a> | `
    let reasons_assigned = _.get( field_settings, "reason_assigned_to.default" );

    if ( tab === "other" ){
        defined_list_section.hide()
        search_section.show()

    } else {
      defined_list_section.show()
      search_section.hide()
      let users_with_role = dispatch_users.filter(u => reasons_assigned[tab].roles.some(r => u.roles.includes(r)))
      let filter_options = {
        all: users_with_role.sort((a,b)=>a.name.localeCompare(b.name)),
        ready: users_with_role.filter(m=>m.status==='active'),
        recent: users_with_role.concat().sort((a,b)=>b.last_assignment-a.last_assignment),
        location: users_with_role.concat().filter(m=>m.location!==null).sort((a,b)=>a.location-b.location)
      }
      populate_user_list( users_with_role )
      filters += filter_options.ready.length ? `<a data-id="ready">${_.escape(roles_settings.translations.ready)}</a> | ` : ''
      filters += filter_options.recent.length ? `<a data-id="recent">${_.escape(roles_settings.translations.recent)}</a> | ` : ''
      filters += filter_options.location.length ? `<a data-id="location">${_.escape(roles_settings.translations.location)}</a> | ` : ''
      list_filters.html(filters)

      $('#user-list-filters a').on('click', function () {
        $( '#user-list-filters a' ).css("color","").css("font-weight","")
        $(this).css("color", "black").css("font-weight", "bold")
        let key = $(this).data('id')
        populate_user_list( filter_options[key] || [] )
      })
    }
  }

  let populated_list = $('.populated-list')
  function populate_user_list( users ){
    let user_rows = '';
    users.forEach( m => {
      user_rows += `<div class="assigned-to-row" dir="auto">
        <span>
          <span class="avatar"><img style="vertical-align: text-bottom" src="${_.escape( m.avatar )}"/></span>
          ${_.escape(m.name)}
        </span>
        ${ m.status_color ? `<span class="status-square" style="background-color: ${ _.escape(m.status_color) }">&nbsp;</span>` : '' }
        ${ m.update_needed ? `
          <span>
            <img style="height: 12px;" src="${_.escape(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
            <span style="font-size: 14px">${ _.escape(m.update_needed) }</span>
          </span>` : '' 
        }
        ${ m.best_location_match ? `<span>(${ _.escape(m.best_location_match) })</span>` : '' 
      
        }
        <div style="flex-grow: 1"></div>
        <button class="button hollow tiny assign-user-button" data-id="${ _.escape(m.ID) }" style="margin-bottom: 3px">
           ${_.escape(roles_settings.translations.assign)}
        </button>
      </div>
      `
    })
    populated_list.html(user_rows)

  }

  $(document).on('click', '.assign-user-button', function () {
    let user_id = $(this).data('id')
    $('#dispatch-tile-loader').addClass('active')
    let selected_reason = _.get(field_settings, `reason_assigned_to.default[${selected_dispatch_tab}]`, {})
    API.update_post(
      'contacts',
      window.contactsDetailsWpApiSettings.contact.ID,
      {
        assigned_to: 'user-' + user_id,
        reason_assigned_to: selected_dispatch_tab,
        overall_status: selected_reason.status || 'assigned'
      }
    ).then(function (response) {
      $('#dispatch-tile-loader').removeClass('active')
      $('#reason_assigned_to').html(`(${selected_reason.label || ''})`)
      setStatus(response)
      $(`.js-typeahead-assigned_to`).val(_.escape(response.assigned_to.display)).blur()
    })
  })

  /**
   * search name in list
   */
  $('#search-users-input').on('input', function () {
    $( '#user-list-filters a' ).css("color","").css("font-weight","")
    let search_text = $(this).val().normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase()
    let reasons_assigned = _.get( field_settings, "reason_assigned_to.default" );
    let users_with_role = dispatch_users.filter(u => reasons_assigned[selected_dispatch_tab].roles.some(r => u.roles.includes(r)))
    let match_name = users_with_role.filter(u =>
      u.name.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase().includes( search_text )
    )
    populate_user_list(match_name)
  })


  /**
   * Assigned_to
   */
  let search_users_input = $(`.js-typeahead-assign`)
  if ( search_users_input.length ){
    $.typeahead({
      input: '.js-typeahead-assign',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      source: TYPEAHEADS.typeaheadUserSource(),
      templateValue: "{{name}}",
      template: function (query, item) {
        return `<div class="assigned-to-row" dir="auto">
            <span>
                <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                ${_.escape( item.name )}
            </span>
            ${ item.status_color ? `<span class="status-square" style="background-color: ${_.escape(item.status_color)};">&nbsp;</span>` : '' }
            ${ item.update_needed.length > 0 ? `<span>
              <img style="height: 12px;" src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
              <span style="font-size: 14px">${_.escape(item.update_needed)}</span>
            </span>` : '' }
          </div>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
      callback: {
        onClick: function(node, a, item){
          API.update_post('contacts', window.contactsDetailsWpApiSettings.contact.ID, {assigned_to: 'user-' + item.ID}).then(function (response) {
            setStatus(response)
            $(`.js-typeahead-assigned_to`).val(_.escape(response.assigned_to.display)).blur()
          }).catch(err => { console.error(err) })
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#assign-result-container').html(text);
        },
        onHideLayout: function () {
          $('.assign-result-container').html("");
        },
        onReady: function () {
          // if (_.get(contact,  "assigned_to.display")){
          //   $('.js-typeahead-assigned_to').val(contact.assigned_to.display)
          // }
          // $('.js-typeahead-assigned_to').trigger('propertychange.typeahead')
          // $('.assigned_to-result-container').html("");
        }
      },
    });


    /**
     * Ready for dispatch button
     */
    $('#mark_dispatch_needed').on("click", function () {
      $('#action-bar-loader').addClass('active')
      $(this).prop("disabled", true)
      API.update_post( "contacts", window.contactsDetailsWpApiSettings.contact.ID, {
        assigned_to: roles_settings.dispatcher_id,
        overall_status: 'unassigned',
        reason_assigned_to: 'dispatch'
      }).then(response=>{
        $('#action-bar-loader').removeClass('active')
        setStatus(response)
        $(`.js-typeahead-assigned_to`).val(_.escape(response.assigned_to.display)).blur()
        $('#reason_assigned_to').html(`(${_.get(field_settings, `reason_assigned_to.default["dispatch"].label`, '')})`)
      })
    })

    /**
     * Claim contact button
     */
    $('#claim').on("click", function () {
      $('#action-bar-loader').addClass('active')
      $(this).prop("disabled", true)
      API.update_post( "contacts", window.contactsDetailsWpApiSettings.contact.ID, {
        assigned_to: window.contactsDetailsWpApiSettings.current_user_id,
        overall_status: 'active',
        reason_assigned_to: 'follow-up'
      }).then(response=>{
        $('#action-bar-loader').removeClass('active')
        setStatus(response)
        $(`.js-typeahead-assigned_to`).val(_.escape(response.assigned_to.display)).blur()
        $('#reason_assigned_to').html(`(${_.get(field_settings, `reason_assigned_to.default["follow-up"].label`, '')})`)
      })
    })
  }

  $('.action-button.quick-action').on('click', function () {
    let fieldKey = $(this).data('id')
    let data = {}
    let numberIndicator = $(this).data('count')
    data[fieldKey] = parseInt(numberIndicator || "0" ) + 1
    $('#action-bar-loader').addClass('active')
    API.update_post('contacts', window.contactsDetailsWpApiSettings.contact.ID, data)
    .then(data=>{
      if (fieldKey.indexOf("quick_button")>-1){
        if (_.get(data, "seeker_path.key")){
          updateCriticalPath(data.seeker_path.key)
        }
      }
      contactUpdated(false)
      $('#action-bar-loader').removeClass('active')
    }).catch(err=>{
      console.log("error")
      console.log(err)
    })
  })

  $('#action-bar .expand-text-descriptions').on('click', function () {
    $('#action-bar .expand-text-descriptions').toggle()
    $('#action-bar .action-text').toggle()
  })

  $(document).on(  "click", `#assigned_to_t .typeahead__item`, function () {
    $('#reason_assigned_to-modal').foundation('open');
  })

  /*
   * Reason assigned to modal update
   */
  let confirmButton = $("#confirm-assign-reason")
  confirmButton.on("click", function () {
    let field = 'reason_assigned_to'
    let select = $(`#reason_assigned_to-options`)
    $(this).toggleClass('loading')
    let val = select.val()
    let data = {
      [field]: val
    }
    API.update_post('contacts', window.contactsDetailsWpApiSettings.contact.ID, data).then(contactData=>{
      $(this).toggleClass('loading')
      $(`#${field}-modal`).foundation('close')
      $('#reason_assigned_to').html(`(${_.get(field_settings, `reason_assigned_to.default[${val}].label`, '')})`)
      setStatus(contactData)
    }).catch(err => { console.error(err) })
  })


  // if ( window.contactsDetailsWpApiSettings.can_view_all){
  //   let assigned_to_input = $(`.js-typeahead-assigned_to`).attr("disabled", true)
  //   $('#assigned_to_t').attr("disabled", true)
  //   $('.search_assigned_to').attr("disabled", true)
  // }

})(window.jQuery, window.roles_settings );
