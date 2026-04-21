import { InertiaCallbacks } from '@/Types/intertia-callbacks';
import { Payload } from '@/Types/payload';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

function options(cb: InertiaCallbacks = {}): InertiaCallbacks {
  return {
    preserveState: true,
    ...cb
  };
}

export default function useTimeTableActions() {
  const url = {
    store: () => route('time-tables.store'),
    update: (timeTableId: number) => route('time-tables.update', { time_table: timeTableId }),
    destroy: (timeTableId: number) => route('time-tables.destroy', { time_table: timeTableId })
  };

  async function add(payload: Payload, cb?: InertiaCallbacks) {
    return router.post(url.store(), payload, options(cb));
  }

  async function update(timeTableId: number, payload: Payload, cb?: InertiaCallbacks) {
    return router.patch(url.update(timeTableId), payload, options(cb));
  }

  async function remove(timeTableId: number, cb?: InertiaCallbacks) {
    return router.delete(url.destroy(timeTableId), options(cb));
  }

  return { add, update, remove };
}
