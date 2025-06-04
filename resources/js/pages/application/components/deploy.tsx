import React, { ReactNode, useState } from 'react';
import { useForm } from '@inertiajs/react';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { LoaderCircleIcon } from 'lucide-react';
import FormSuccessful from '@/components/form-successful';
import { Site } from '@/types/site';

export default function Deploy({ site, children }: { site: Site; children: ReactNode }) {
  const [open, setOpen] = useState(false);
  const form = useForm();

  const submit = () => {
    form.post(route('application.deploy', { server: site.server_id, site: site.id }), {
      onSuccess: () => {
        setOpen(false);
      },
    });
  };
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Deploy</DialogTitle>
          <DialogDescription className="sr-only">Deploy application</DialogDescription>
        </DialogHeader>
        <div className="space-y-2 p-4">
          <p>Are you sure you want to deploy this site?</p>
        </div>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Cancel</Button>
          </DialogClose>
          <Button disabled={form.processing} onClick={submit}>
            {form.processing && <LoaderCircleIcon className="animate-spin" />}
            <FormSuccessful successful={form.recentlySuccessful} />
            Deploy
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
