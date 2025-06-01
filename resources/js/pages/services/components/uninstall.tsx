import { Service } from '@/types/service';
import React, { useState } from 'react';
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
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { LoaderCircleIcon } from 'lucide-react';
import FormSuccessful from '@/components/form-successful';

export default function Uninstall({ service }: { service: Service }) {
  const [open, setOpen] = useState(false);
  const form = useForm();

  const submit = () => {
    form.delete(route('services.destroy', { server: service.server_id, service: service }), {
      onSuccess: () => {
        setOpen(false);
      },
    });
  };
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <DropdownMenuItem variant="destructive" onSelect={(e) => e.preventDefault()}>
          Uninstall
        </DropdownMenuItem>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Uninstall service</DialogTitle>
          <DialogDescription className="sr-only">Uninstall service</DialogDescription>
        </DialogHeader>
        <p className="p-4">Are you sure you want to uninstall this service? This action cannot be undone.</p>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Cancel</Button>
          </DialogClose>
          <Button variant="destructive" disabled={form.processing} onClick={submit}>
            {form.processing && <LoaderCircleIcon className="animate-spin" />}
            <FormSuccessful successful={form.recentlySuccessful} />
            Uninstall
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
