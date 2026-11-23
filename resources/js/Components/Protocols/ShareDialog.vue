<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Dialog, InputText, useConfirm } from 'primevue';
import { nextTick, ref } from 'vue';
import useAlert from '@/Composables/use-alerts';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  protocolSlug: string;
  shareUrl: string | null;
  canManage: boolean;
}>();

const visible = defineModel<boolean>('visible', { required: true });

const alert = useAlert();
const confirm = useConfirm();
const processing = ref(false);
const linkInput = ref<{ $el: HTMLInputElement } | null>(null);

const visit = (method: 'post' | 'delete', name: string, onSuccess?: () => void) => {
  router.visit(route(name, { protocol: props.protocolSlug }), {
    method,
    preserveScroll: true,
    // Keeps the page's local state, so the dialog stays open with the new link
    preserveState: true,
    onStart: () => (processing.value = true),
    onFinish: () => (processing.value = false),
    onSuccess
  });
};

const createLink = () =>
  visit('post', 'protocols.share', async () => {
    // Wait until the link field is rendered, the legacy copy needs it
    await nextTick();

    const copied = props.shareUrl ? await writeToClipboard(props.shareUrl) : false;
    alert.add(trans(copied ? 'Share link created and copied.' : 'Share link created.'), 'success');
  });

const copyLink = async () => {
  if (!props.shareUrl) return;

  if (await writeToClipboard(props.shareUrl)) {
    alert.add(trans('Link copied.'), 'success');
  } else {
    alert.add(trans('Could not copy the link.'), 'warn');
  }
};

const writeToClipboard = async (text: string): Promise<boolean> => {
  // The Clipboard API only exists on HTTPS and localhost
  if (navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch {
      // Fall through to the legacy copy
    }
  }

  // Legacy copy for plain HTTP: copies the selected text of the link field
  const input = linkInput.value?.$el;
  if (!input) return false;
  input.focus();
  input.select();

  try {
    return document.execCommand('copy');
  } catch {
    return false;
  }
};

const renewLink = () => {
  confirm.require({
    header: trans('New link'),
    message: trans('The current link stops working. Create a new link?'),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('New link'),
    rejectLabel: trans('Cancel'),
    accept: createLink
  });
};

const stopSharing = () => {
  confirm.require({
    header: trans('Stop sharing'),
    message: trans('Stop sharing the protocol? The link stops working.'),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('Stop sharing'),
    rejectLabel: trans('Cancel'),
    acceptClass: 'p-button-danger',
    accept: () => visit('delete', 'protocols.unshare')
  });
};
</script>

<template>
  <Dialog
    v-model:visible="visible"
    modal
    :header="$t('Share protocol')"
    class="w-full max-w-lg mx-4"
  >
    <template v-if="shareUrl">
      <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
        {{
          $t(
            'Anyone with this link can read the protocol without logging in. Author and history stay hidden.'
          )
        }}
      </p>
      <div class="flex gap-2">
        <InputText
          ref="linkInput"
          :model-value="shareUrl"
          readonly
          class="flex-1 min-w-0"
          :aria-label="$t('Share')"
          @focus="($event.target as HTMLInputElement).select()"
        />
        <Button :label="$t('Copy link')" icon="pi pi-copy" @click="copyLink" />
      </div>
      <div v-if="canManage" class="mt-4 flex flex-wrap justify-end gap-2">
        <Button
          :label="$t('New link')"
          icon="pi pi-refresh"
          text
          :disabled="processing"
          @click="renewLink"
        />
        <Button
          :label="$t('Stop sharing')"
          icon="pi pi-times"
          severity="danger"
          text
          :disabled="processing"
          @click="stopSharing"
        />
      </div>
    </template>

    <template v-else>
      <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        {{
          $t('The protocol is not shared yet. A share link lets anyone read it without logging in.')
        }}
      </p>
      <div class="flex justify-end">
        <Button
          v-if="canManage"
          :label="$t('Create share link')"
          icon="pi pi-link"
          :loading="processing"
          @click="createLink"
        />
      </div>
    </template>
  </Dialog>
</template>
