<h2>{$titulo}</h2>

<input type='hidden' id='adminToken' value='{$adminToken}'>
<input type='hidden' id='urlAdminOrder' value='{$urlAdminOrder}'>

<br>

{if $is_recovery_cart}
    <div id="abantadoned_content">
        {if $errorMsg && count($errorMsg)}
            <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="search_abandoned_button">{l s='Realizar Nova Pesquisa'}</a>
            {foreach from=$errorMsg key=error_key item=error_value}
                <div class="error">
                        {$error_value}
                </div>
            {/foreach}
        {else}
        <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="search_abandoned_button">{l s='Pesquisar'}</a>
        <a href="javascript:void(0)" class="pagseguro-button green-theme normal" id="send_email_button">{l s='Enviar por demanda'}</a>
        
            <table class='gridConciliacao' width='100%' id="my_table_abandoned_orders">
                <thead>
                    <tr>
                        <th></th>
                        <th>Data Cria��o do Pedido</th>
                        <th>Id ordem prestashop</th>
                        <th>Data validade do link</th>
                        <th>Enviar email</th>
                        <th></th>
                    </tr>
                </thead>
                <tfoot> 
                    <tr> 
                      <th colspan="6">Validade dos link para envio de email:  {$day_recovery_teste} dias</th> 
                    </tr> 
                </tfoot>
                <tbody>
                    {if $abandoned_orders && count($abandoned_orders)}
                        {foreach from=$abandoned_orders key=key_order item=value_order}
                            <tr>
                                <td align="center"><input type="checkbox" id="send_{$key_order}" name="send_emails[]" value="customer={$value_order.customer}&reference={$value_order.reference}&recovery={$value_order.recovery_code}"></td>
                                <td align="center" class="bold">{$value_order.data_add_cart|date_format:"%d/%m/%Y"}</td>
                                <td align="center" class="bold">{l s='#'}{$value_order.reference|string_format:"%06d"}</td>
                                <td align="center" class="bold">{$value_order.data_expired}</td>
                                <td align="center"> <a href="javascript:void(0)" onclick="javascript:meutesteajax('customer={$value_order.customer}&reference={$value_order.reference}&recovery={$value_order.recovery_code}');"> <img src="../img/admin/email.gif" title="{l s='enviar email'}"/> </a> </td>
                                <td align="center"><a href="?tab=AdminOrders&id_order={$value_order.reference}&vieworder&token={$adminToken}"><img src="../img/admin/details.gif" title="{l s='visualizar ordem'}"/></a></td> </tr>
                            </tr>
                         {/foreach}
                	{else}
                        <tr>
                            <td colspan="6" align="center">{l s='Nenhum resultado encontrado.'}</td>
                        </tr>
                	{/if}        
                </tbody>
            </table>
        {/if}
    </div>
{else}
    <div class="warn">
        Ative a op��o "Recupera��o de Carrinho" para poder desfrutar a nova funcionalidade.
    </div>
{/if}

<script type="text/javascript">

    $('#send_email_button').click(function () {
        blockModal(1);

        var checkboxValues = new Array();
        $('input[name="send_emails[]"]:checked').each(function() {
        	checkboxValues.push($(this).val());
        });

        $.ajax({
            type: "GET",
            url: '{$base_dir}modules/pagseguro/features/abandoned/ajax-abandoned.php',
            data: 'action=multiemails&'+$('input[name="send_emails[]"]').serialize(),
            success: function(response) {
                blockModal(0);
                $( "table:first" )
                    .next()
                    .after(response);
            }
        });
        return false;
    });

    $('#search_abandoned_button').click(function () {
        blockModal(1);        
        $.ajax({
            type: "GET",
            url: '{$base_dir}modules/pagseguro/features/abandoned/ajax-abandoned.php',
            dataType : "html",
            data: 'action=searchtable',
            success: function(response) {
                $('#menuTab4Sheet').empty();
                $('#menuTab4Sheet').append(response);
                blockModal(0);
            }
        });
        return false;
    });

    function meutesteajax(content) {
        blockModal(1);        
        $.ajax({
            type: "GET",
            url: '{$base_dir}modules/pagseguro/features/abandoned/ajax-abandoned.php',
            data: 'action=singleemail&'+content,
            success: function(response) {
                blockModal(0);
                $( "table:first" )
                    .next()
                    .after(response);
                    
            }
        });
        return false;
    }

</script>