export type InertiaCallbacks = Partial<{
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
