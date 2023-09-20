{* HEADER *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<div class="crm-section">
  <h2>Huidige organisator en partners</h2>
  <table>
      <tr>
        <th>Naam</th>
        <th>Rol</th>
        <th></th>
      </tr>
      {foreach from=$partners item=partner}
        <tr>
          <td>{$partner.name}</td>
          <td>{$partner.role}</td>
          <td><a href="{$partner.edit_link}">Bewerk</a></td>
        </tr>
      {/foreach}
  </table>
 </div>
