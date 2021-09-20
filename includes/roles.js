"use strict";
(function($, roles_settings) {

  const isDispatcher = window.lodash.get(roles_settings, "is_dispatcher") && roles_settings.is_dispatcher !== ""
  const isAssignedToEnabled = window.lodash.get(roles_settings, "roles_settings.assigned_to.enabled") === undefined || roles_settings.roles_settings.assigned_to.enabled === true && isDispatcher

  let field_settings = window.detailsSettings.post_settings.fields

  /**
   * Ready for dispatch button
   */
  $('#mark_dispatch_needed').on("click", function () {
    $('#action-bar-loader').addClass('active')
    $(this).prop("disabled", true)
    API.update_post( "contacts", window.detailsSettings.post_fields.ID, {
      assigned_to: roles_settings.dispatcher_id,
      overall_status: 'unassigned',
      reason_assigned_to: 'dispatch'
    }).then(response=>{
      $('#action-bar-loader').removeClass('active')
      setStatus(response)
      $(`.js-typeahead-assigned_to`).val(window.lodash.escape(response.assigned_to.display)).blur()
      $('#reason_assigned_to').html(`(${window.lodash.get(field_settings, `reason_assigned_to.default["dispatch"].label`, '')})`)
    })
  })

  /**
   * Claim contact button
   */
  $('#claim').on("click", function () {
    $('#action-bar-loader').addClass('active')
    $(this).prop("disabled", true)
    API.update_post( "contacts", window.detailsSettings.post_fields.ID, {
      assigned_to: window.detailsSettings.current_user_id,
      overall_status: 'active',
      reason_assigned_to: 'follow-up'
    }).then(response=>{
      $('#action-bar-loader').removeClass('active')
      setStatus(response)
      $(`.js-typeahead-assigned_to`).val(window.lodash.escape(response.assigned_to.display)).blur()
      $('#reason_assigned_to').html(`(${window.lodash.get(field_settings, `reason_assigned_to.default["follow-up"].label`, '')})`)
    })
  })

  $('.action-button.quick-action').on('click', function () {
    let fieldKey = $(this).data('id')
    let data = {}
    let numberIndicator = $(this).data('count')
    data[fieldKey] = parseInt(numberIndicator || "0" ) + 1
    $('#action-bar-loader').addClass('active')
    API.update_post('contacts', window.detailsSettings.post_fields.ID, data)
    .then(data=>{
      if (fieldKey.indexOf("quick_button")>-1){
        if (window.lodash.get(data, "seeker_path.key")){
          updateCriticalPath(data.seeker_path.key)
        }
      }
      record_updated(false)
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


  /* Turn off typeahead dropdown button if assigned to  */
  if ( isAssignedToEnabled ) {
    $(document).on(  "click", `#assigned_to_t .typeahead__item`, function () {
      $('#reason_assigned_to-modal').foundation('open');
    })

  }


  /*
   * Reason assigned to modal update
   */
  $('#reason_assigned_to-options button').on("click", function (){
    let field = 'reason_assigned_to'
    $('#reason_assigned_to-modal .loading-spinner').addClass('active')
    let val = $(this).attr('id')
    let selected_reason = window.lodash.get(field_settings, `reason_assigned_to.default[${val}]`, {})
    let data = {
      "overall_status": selected_reason.status || 'assigned',
      [field]: val
    }

    API.update_post('contacts', window.detailsSettings.post_fields.ID, data).then(contactData=>{
      $('#reason_assigned_to').html(selected_reason.label)
      setStatus(contactData)
    }).catch(err => {
      console.error(err)
    }).then(()=>{
      $(`#${field}-modal`).foundation('close')
      $('#reason_assigned_to-modal .loading-spinner').removeClass('active')
    })
  })

})(window.jQuery, window.roles_settings );
