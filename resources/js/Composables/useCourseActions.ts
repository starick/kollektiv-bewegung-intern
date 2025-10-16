import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

type Payload = Record<string, any>;
type InertiaCallbacks = Partial<{
  onCancelToken: {
    ({ cancel }: { cancel: VoidFunction }): void;
  };
  onBefore: () => void;
  onStart: () => void;
  onProgress: () => void;
  onCancel: () => void;
  onSuccess: () => void;
  onError: (errors: any) => void;
  onFinish: () => void;
  preserveState: boolean;
  only: string[];
}>;

function options(cb: InertiaCallbacks = {}): InertiaCallbacks {
  return {
    preserveState: true,
    ...cb
  };
}

export default function useCourseActions(timeTableId: number) {
  const url = {
    store: () => route('time-tables.courses.store', { time_table: timeTableId }),
    update: (courseId: number) =>
      route('time-tables.courses.update', { time_table: timeTableId, course: courseId }),
    destroy: (courseId: number) =>
      route('time-tables.courses.destroy', { time_table: timeTableId, course: courseId })
  };

  function add(payload: Payload, cb?: InertiaCallbacks) {
    router.post(url.store(), payload, options(cb));
  }

  function edit(courseId: number, payload: Payload, cb?: InertiaCallbacks) {
    router.put(url.update(courseId), payload, options(cb));
  }

  function remove(courseId: number, cb?: InertiaCallbacks) {
    router.delete(url.destroy(courseId), options(cb));
  }

  function reload(cb?: InertiaCallbacks) {
    router.reload({ only: ['courses'], ...cb });
  }

  return { add, edit, remove, reload };
}
