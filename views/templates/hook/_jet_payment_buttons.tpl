{if $jet_button_type == 'wide'}
    <div class="jet-wide-buttons-stack">
        <div class="jet-wide-button-wrap" style="{$jet_wide_wrap_style|escape:'html':'UTF-8'}">
            <div id="btn_jet" class="jet-wide-button jet_logo" role="button" tabindex="0"
                title="Кредитен модул ПБ Лични Финанси" aria-label="Кредитен модул ПБ Лични Финанси">
                <div class="jet-wide-button-head">
                    <span class="jet-wide-button-label">{$jet_btn_text|escape:'html':'UTF-8'}</span>
                    {if $jet_btn_logo == 1}
                        <img src="{$module_dir}views/templates/img/jet_logo.png" alt="ПБ Лични Финанси"
                            class="jet-wide-button-logo" />
                    {/if}
                </div>
                {if $is_vnoska == 1}
                    <div class="jet-wide-button-text">
                        <span id="jet_vnoski_text"></span> x <span id="jet_vnoska"></span> {$jet_sign}
                        {if $jet_sign_second != ''}
                            <span class="jet-wide-button-text-second">(<span id="jet_vnoska_second"></span>
                                {$jet_sign_second})</span>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
        {if $jet_card_in == 1}
            <div class="jet-wide-button-wrap" style="{$jet_wide_wrap_style|escape:'html':'UTF-8'}">
                <div id="btn_jet_card" class="jet-wide-button jet_logo" role="button" tabindex="0"
                    title="Специални предложения само за клиенти, които вече имат кредитна карта, издадена от ПБ Лични Финанси."
                    aria-label="Специални предложения само за клиенти, които вече имат кредитна карта, издадена от ПБ Лични Финанси.">
                    <div class="jet-wide-button-head">
                        <span class="jet-wide-button-label">{$jet_btn_text_card|escape:'html':'UTF-8'}</span>
                        {if $jet_btn_logo == 1}
                            <img src="{$module_dir}views/templates/img/jet_logo.png" alt="ПБ Лични Финанси"
                                class="jet-wide-button-logo" />
                        {/if}
                    </div>
                    {if $is_vnoska == 1}
                        <div class="jet-wide-button-text">
                            <span id="jet_vnoski_text_card"></span> x <span id="jet_vnoska_card"></span> {$jet_sign}
                            {if $jet_sign_second != ''}
                                <span class="jet-wide-button-text-second">(<span id="jet_vnoska_card_second"></span>
                                    {$jet_sign_second})</span>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
{else}
    <table class="jet_table_img">
        <tr>
            <td class="jet_button_table">
                <img id="btn_jet" class="jet_logo" src="{$module_dir}views/templates/img/jet.png"
                    alt="Кредитен модул ПБ Лични Финанси" title="Кредитен модул ПБ Лични Финанси" />
            </td>
        </tr>
        {if $is_vnoska == 1}
            <tr>
                <td class="jet_button_table">
                    {if $jet_sign_second == ''}
                        <p><span id="jet_vnoski_text"></span> x <span id="jet_vnoska"></span> {$jet_sign}</p>
                    {else}
                        <p>
                            <span id="jet_vnoski_text"></span> x <span id="jet_vnoska"></span> {$jet_sign}<br />
                            <span style="font-size:75%;font-weight:400;">(<span id="jet_vnoska_second"></span>
                                {$jet_sign_second})</span>
                        </p>
                    {/if}
                </td>
            </tr>
        {/if}
    </table>
    {if $jet_card_in == 1}
        <table class="jet_table_img">
            <tr>
                <td class="jet_button_table">
                    <img id="btn_jet_card" class="jet_logo" src="{$module_dir}views/templates/img/jet_card.png"
                        alt="Специални предложения само за клиенти, които вече имат кредитна карта, издадена от ПБ Лични Финанси."
                        title="Специални предложения само за клиенти, които вече имат кредитна карта, издадена от ПБ Лични Финанси." />
                </td>
            </tr>
            {if $is_vnoska == 1}
                <tr>
                    <td class="jet_button_table">
                        {if $jet_sign_second == ''}
                            <p><span id="jet_vnoski_text_card"></span> x <span id="jet_vnoska_card"></span> {$jet_sign}</p>
                        {else}
                            <p>
                                <span id="jet_vnoski_text_card"></span> x <span id="jet_vnoska_card"></span> {$jet_sign}<br />
                                <span style="font-size:75%;font-weight:400;">(<span id="jet_vnoska_card_second"></span>
                                    {$jet_sign_second})</span>
                            </p>
                        {/if}
                    </td>
                </tr>
            {/if}
        </table>
    {/if}
{/if}