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
import { FormEvent, ReactNode, useState } from 'react';
import { Form, FormField, FormFields } from '@/components/ui/form';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/react';
import { LoaderCircleIcon } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { FirewallRule } from '@/types/firewall';

export default function RuleForm({ serverId, firewallRule, children }: { serverId: number; firewallRule?: FirewallRule; children: ReactNode }) {
  const [open, setOpen] = useState(false);
  const form = useForm<{
    name: string;
    type: string;
    protocol: string;
    port: string;
    source_any: boolean;
    source: string;
    mask: string;
  }>({
    name: firewallRule?.name || '',
    type: firewallRule?.type || '',
    protocol: firewallRule?.protocol || '',
    port: firewallRule?.port?.toString() || '',
    source_any: !firewallRule?.source,
    source: firewallRule?.source || '',
    mask: firewallRule?.mask?.toString() || '',
  });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    if (firewallRule) {
      form.put(route('firewall.update', { server: serverId, firewallRule: firewallRule.id }), {
        onSuccess: () => {
          setOpen(false);
          form.reset();
        },
      });
      return;
    }

    form.post(route('firewall.store', { server: serverId }), {
      onSuccess: () => {
        setOpen(false);
        form.reset();
      },
    });
  };
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{firewallRule ? 'Edit' : 'Create'} firewall rule</DialogTitle>
          <DialogDescription className="sr-only">{firewallRule ? 'Edit' : 'Create new'} firewall rule</DialogDescription>
        </DialogHeader>
        <Form id="firewall-rule-form" onSubmit={submit} className="p-4">
          <FormFields>
            <FormField>
              <Label htmlFor="name">Name</Label>
              <Input type="text" id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
              <InputError message={form.errors.name} />
            </FormField>

            <FormField>
              <Label htmlFor="type">Type</Label>
              <Select onValueChange={(value) => form.setData('type', value)} value={form.data.type}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem value="allow">Allow</SelectItem>
                    <SelectItem value="deny">Deny</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <InputError message={form.errors.type} />
            </FormField>

            <FormField>
              <Label htmlFor="protocol">Protocol</Label>
              <Select onValueChange={(value) => form.setData('protocol', value)} value={form.data.protocol}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select protocol" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem value="tcp">TCP</SelectItem>
                    <SelectItem value="udp">UDP</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <InputError message={form.errors.protocol} />
            </FormField>

            <FormField>
              <Label htmlFor="port">Port</Label>
              <Input type="text" id="port" value={form.data.port} onChange={(e) => form.setData('port', e.target.value)} />
              <InputError message={form.errors.port} />
            </FormField>

            <FormField>
              <div className="flex items-center space-x-3">
                <Checkbox id="source_any" checked={form.data.source_any} onClick={() => form.setData('source_any', !form.data.source_any)} />
                <Label htmlFor="source_any">Any source</Label>
              </div>
            </FormField>

            {!form.data.source_any && (
              <>
                <FormField>
                  <Label htmlFor="source">Source</Label>
                  <Input type="text" id="source" value={form.data.source} onChange={(e) => form.setData('source', e.target.value)} />
                  <InputError message={form.errors.source} />
                </FormField>

                <FormField>
                  <Label htmlFor="mask">Mask</Label>
                  <Input type="text" id="mask" value={form.data.mask} onChange={(e) => form.setData('mask', e.target.value)} />
                  <InputError message={form.errors.mask} />
                </FormField>
              </>
            )}
          </FormFields>
        </Form>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Close</Button>
          </DialogClose>
          <Button form="firewall-rule-form" type="submit" disabled={form.processing}>
            {form.processing && <LoaderCircleIcon className="animate-spin" />}
            Save
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
