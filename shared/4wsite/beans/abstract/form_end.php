   <? if (!$next_action_fields) $next_action_fields = 'action, what2do, record_id, page'; ?><?= $query->fieldsFor ($next_action_fields); ?>
   <? if (!$next_action) $next_action = 'execute-task'; ?><input type=hidden name="action" value="<?= $next_action ?>">
   </form>
