import React, { FormEvent, ReactNode, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Editor, useMonaco } from '@monaco-editor/react';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Form } from '@/components/ui/form';
import { Button } from '@/components/ui/button';
import { LoaderCircleIcon } from 'lucide-react';
import { Site } from '@/types/site';
import { registerBashLanguage } from '@/lib/editor';
import InputError from '@/components/ui/input-error';
import { useAppearance } from '@/hooks/use-appearance';

export default function DeploymentScript({ site, script, children }: { site: Site; script: string; children: ReactNode }) {
  const { getActualAppearance } = useAppearance();

  const [open, setOpen] = useState(false);
  const form = useForm<{
    script: string;
  }>({
    script: script,
  });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.put(route('application.update-deployment-script', { server: site.server_id, site: site.id }), {
      onSuccess: () => {
        setOpen(false);
      },
    });
  };

  registerBashLanguage(useMonaco());

  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger asChild>{children}</SheetTrigger>
      <SheetContent className="sm:max-w-5xl">
        <SheetHeader>
          <SheetTitle>Deployment script</SheetTitle>
          <SheetDescription className="sr-only">Update deployment script</SheetDescription>
        </SheetHeader>
        <Form id="update-script-form" className="h-full" onSubmit={submit}>
          <Editor
            defaultLanguage="bash"
            defaultValue={form.data.script}
            theme={getActualAppearance() === 'dark' ? 'vs-dark' : 'vs'}
            className="h-full"
            onChange={(value) => form.setData('script', value ?? '')}
            options={{
              fontSize: 15,
            }}
          />
        </Form>
        <SheetFooter>
          <div className="flex items-center gap-2">
            <Button form="update-script-form" disabled={form.processing} onClick={submit} className="ml-2">
              {form.processing && <LoaderCircleIcon className="animate-spin" />}
              Save
            </Button>
            <SheetClose asChild>
              <Button variant="outline">Cancel</Button>
            </SheetClose>
            <InputError message={form.errors.script} />
          </div>
        </SheetFooter>
      </SheetContent>
    </Sheet>
  );
}
