import { afterEach, describe, expect, it, vi } from 'vitest';
import { nextTick, ref } from 'vue';
import axios from 'axios';
import { useMarkdownPreview } from '@/Composables/use-markdown-preview';

vi.mock('axios', () => ({ default: { post: vi.fn() } }));
vi.mock('ziggy-js', () => ({ route: (name: string) => `/${name}` }));
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({
    props: { translations: { 'The preview could not be loaded.': 'Vorschau fehlgeschlagen' } }
  })
}));

const post = vi.mocked(axios.post);

function deferred<T>() {
  let resolve!: (value: T) => void;
  let reject!: (reason?: unknown) => void;
  const promise = new Promise<T>((res, rej) => {
    resolve = res;
    reject = rej;
  });
  return { promise, resolve, reject };
}

// Lets watchers run and awaited promises settle
const settle = async () => {
  await nextTick();
  await new Promise((resolve) => setTimeout(resolve));
};

afterEach(() => {
  post.mockReset();
});

describe('useMarkdownPreview', () => {
  it('renders the text when switching to preview', async () => {
    post.mockResolvedValue({ data: { html: '<h1>Hi</h1>' } });
    const text = ref('# Hi');
    const preview = useMarkdownPreview(text);

    preview.mode.value = 'preview';
    await settle();

    expect(post).toHaveBeenCalledWith('/markdown.preview', { text: '# Hi' });
    expect(preview.previewHtml.value).toBe('<h1>Hi</h1>');
    expect(preview.previewLoading.value).toBe(false);
  });

  it('does not render unchanged text again', async () => {
    post.mockResolvedValue({ data: { html: '<h1>Hi</h1>' } });
    const preview = useMarkdownPreview(ref('# Hi'));

    preview.mode.value = 'preview';
    await settle();
    preview.mode.value = 'edit';
    await settle();
    preview.mode.value = 'preview';
    await settle();

    expect(post).toHaveBeenCalledTimes(1);
  });

  it('keeps the newest preview when an older response arrives last', async () => {
    const first = deferred<{ data: { html: string } }>();
    const second = deferred<{ data: { html: string } }>();
    post.mockReturnValueOnce(first.promise as never).mockReturnValueOnce(second.promise as never);
    const text = ref('old');
    const preview = useMarkdownPreview(text);

    preview.mode.value = 'preview';
    await settle();
    preview.mode.value = 'edit';
    text.value = 'new';
    await settle();
    preview.mode.value = 'preview';
    await settle();

    second.resolve({ data: { html: '<p>new</p>' } });
    await settle();
    first.resolve({ data: { html: '<p>old</p>' } });
    await settle();

    expect(preview.previewHtml.value).toBe('<p>new</p>');
    expect(preview.previewLoading.value).toBe(false);
  });

  it('shows an error when the preview fails', async () => {
    post.mockRejectedValue(new Error('offline'));
    const preview = useMarkdownPreview(ref('# Hi'));

    preview.mode.value = 'preview';
    await settle();

    expect(preview.previewError.value).toBe('Vorschau fehlgeschlagen');
    expect(preview.previewLoading.value).toBe(false);
  });
});
